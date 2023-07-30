<?php

namespace App\Commands;

use App\Services\WpCli;
use App\Services\SiteIndex;
use Illuminate\Support\Str;
use App\Services\WpCoreVersion;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

class Add extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'add {name} {--wp-version=latest : Version can be a verison number, "latest" or "nightly"} {--path=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Creates a site';

    /**
     * The version to install/use.
     */
    protected string $version;

    /**
     * The path to install to - will default to a subdirectory of the current
     * directory using the site name.
     */
    protected string $installPath;

    /**
     * Get the version option - interprets 'latest' as the latest version.
     */
    public function getVersionOption() : string
    {
        return $this->option('wp-version');
    }

    /**
     * Get the install path option - defaults to a subdirectory of the current directory.
     */
    public function getInstallPathOption() : string
    {
        return $this->option('path') ?? ( getcwd() . '/' . $this->argument('name') );
    }

    /**
     * Link the core files to the sites directory.
     */
    public function linkCoreFiles(): void
    {
        $filesToLink = [
            '/wp-admin',
            '/wp-includes',
            '/wp-*.php',
            '/index.php',
            '/xmlrpc.php',
        ];

        $coreFilesPath = app(WpCoreVersion::class)->getPath($this->version);

        $pathsToLink = array_map(
            fn ($file) => $coreFilesPath . $file,
            $filesToLink
        );

        // TODO: Error handling
        exec('ln -s ' . implode(' ', $pathsToLink) . ' ' . $this->installPath, $output, $resultCode);
    }

    /**
     * Create the wp-config.php file.
     */
    public function createWpConfigFile(): void
    {
        // TODO: Error handling
        app(WpCli::class)->run('config create --dbname=localhost --dbuser=unused --skip-check --insecure --path=' . $this->installPath);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(SiteIndex $index)
    {
        $this->version = $this->getVersionOption();

        $this->installPath = $this->getInstallPathOption();

        // CHECK NAME AND PATH DON'T ALREADY EXIST
        // Check for an existing directory
        if (File::isDirectory($this->installPath)) {
            $this->error("Directory already exists: " . $this->installPath);
            return;
        }

        // Check for an existing site in the index
        if ($index->exists($this->argument('name'))) {
            $this->error("Site already exists: " . $this->argument('name'));
            return;
        }

        $this->info("Adding site: " . $this->argument('name'));

        // Make the directory
        mkdir($this->installPath);

        // Link the core files to the sites directory
        $this->linkCoreFiles();

        // or wp config create
        $this->createWpConfigFile();

        // make wp-content directory
        File::ensureDirectoryExists($this->installPath . '/wp-content');
        // make wp-content/plugins
        File::ensureDirectoryExists($this->installPath . '/wp-content/plugins');
        // make wp-content/themes
        File::ensureDirectoryExists($this->installPath . '/wp-content/themes');

        // Check SQLite plugin exists and get it if required
        File::ensureDirectoryExists(Storage::path('/plugins'));
        if (Storage::exists('/plugins/sqlite-database-integration.zip')) {
            $this->info("Using existing SQLite plugin");
        } else {
            $this->info("Fetching SQLite plugin");
            Http::withOptions(['sink' => Storage::path('plugins/sqlite-database-integration.zip')])
                ->get('https://downloads.wordpress.org/plugin/sqlite-database-integration.zip');
            // Unzip the plugin
            $this->info("Unzipping SQLite plugin");
            $zip = new \ZipArchive;
            $zip->open(Storage::path('plugins/sqlite-database-integration.zip'));
            $zip->extractTo(Storage::path('plugins'));
        }

        File::copyDirectory(Storage::path('plugins/sqlite-database-integration'), $this->installPath . '/wp-content/plugins/sqlite-database-integration');

        File::copy(Storage::path('plugins/sqlite-database-integration/db.copy'), $this->installPath . '/wp-content/db.php');

        // from https://github.com/WordPress/sqlite-database-integration/issues/7#issuecomment-1563465590
        $dbPhp = file_get_contents($this->installPath . '/wp-content/db.php');
        // Replace the placeholders with the correct values
        $dbPhp = str_replace(
            [
                '{SQLITE_IMPLEMENTATION_FOLDER_PATH}',
                '{SQLITE_PLUGIN}',
            ],
            [
                $this->installPath . '/wp-content/plugins/sqlite-database-integration',
                'sqlite-database-integration/load.php',
            ],
            $dbPhp
        );
        File::makeDirectory($this->installPath . '/wp-content/database');
        File::put(
            $this->installPath . '/wp-content/database/.ht.sqlite',
            ''
        );

        // copy in a theme?
        // TODO: Make this a method of the CoreVersion service
        $themeDirs = Storage::directories('wordpress/' . $this->version . '/wp-content/themes');
        $this->info('Copying default themes');
        foreach ($themeDirs as $dir) {
            File::copyDirectory(
                Storage::path($dir),
                $this->installPath . '/wp-content/themes/' . Str::afterLast($dir, '/')
            );
        }

        // Could add --locale
        app(WpCli::class)->run('core install --url=http://localhost:8001 --title="' . $this->argument('name') . '" --admin_user=admin --admin_password=admin --admin_email=admin@example.com --skip-email --path=' . $this->installPath);

        // copy the router.php in
        File::copy(
            app_path('router-template.php'),
            $this->installPath . '/router.php'
        );

        $index->add($this->argument('name'), $this->installPath, $this->version);

        $this->info("Starting site on http://localhost:8001 - press Ctrl+C to stop - wp-admin login is admin/admin");

        // Note that this is not Laravel 10 yet so we don't have the Process Facade and are using the Symfony Process component directly
        Process::fromShellCommandline("php -S localhost:8001 $this->installPath/router.php", $this->installPath, timeout: null)->run();
    }
}
