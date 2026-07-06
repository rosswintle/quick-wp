<?php

namespace App\Commands;

use App\Services\SiteIndex;
use App\Services\UserStorage;
use App\Services\WpCli;
use App\Services\WpCoreVersion;
use App\Site;
use App\Traits\GetsInstallPath;
use App\Traits\InstallsRouterScript;
use App\Traits\InstallsSQLitePlugin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Concerns\ValidatesAttributes;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\Process;

class Add extends Command
{
    use GetsInstallPath;
    use InstallsRouterScript;
    use InstallsSQLitePlugin;
    use ValidatesAttributes;

    /**
     * The signature of the command.
     *
     * If you update this, remember to update the create command too.
     *
     * @var string
     */
    protected $signature = 'add {name}
    {--wp-version=latest : Version can be a verison number, "latest" or "nightly"}
    {--path= : A path to install to. Defaults to a subdirectory of the current directory or the configured default path. }
    {--hostname='.Site::DEFAULT_HOSTNAME.' : The hostname to use}
    {--port='.Site::DEFAULT_PORT.' : The port to use}
    {--plugins= : Comma separated list of plugins to be installed}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Creates a site';

    /**
     * The version requested to install/use.
     *
     * Can be a version number, "latest", "nightly", "beta" or "rc"
     */
    protected string $requestedVersion;

    /**
     * The actual version number to install/use.
     */
    protected string $actualVersion;

    /**
     * The path to install to - will default to a subdirectory of the current
     * directory using the site name.
     */
    protected string $installPath;

    /**
     * The hostname to run the site on
     */
    protected string $hostname = Site::DEFAULT_HOSTNAME;

    /**
     * The port to run the site on
     */
    protected int $port = Site::DEFAULT_PORT;

    /**
     * Validates arguments (the mandatory/positional options) *before* processing them
     */
    public function validateArguments(): void
    {
        if (! $this->validateAlphaDash('name', $this->argument('name'), true)) {
            $this->error('Site name can only contain letters, numbers and dashes');
            exit();
        }

        if (! $this->validateRegex('hostname', $this->option('hostname'), ['/^[a-zA-Z0-9\.\-_]*$/'])) {
            $this->error('Hostname can only contain letters, numbers, dots, underscored and dashes');
            exit();
        }

        if (! $this->validateInteger('port', $this->option('port'))) {
            $this->error('Port must be a number');
            exit();
        }
    }

    public function validateOptions(): void
    {
        // TODO: Validate options
    }

    /**
     * Get the version option - interprets 'latest' as the latest version.
     */
    public function getVersionOption(): string
    {
        return $this->option('wp-version');
    }

    /**
     * Get the hostname option
     */
    public function getHostnameOption(): string
    {
        return $this->option('hostname');
    }

    /**
     * Get the port option
     */
    public function getPortOption(): string
    {
        return $this->option('port');
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

        $coreFilesPath = app(WpCoreVersion::class)->getPath($this->requestedVersion, $this->actualVersion);

        $pathsToLink = array_map(
            fn ($file) => $coreFilesPath.$file,
            $filesToLink
        );

        // TODO: Error handling
        exec('ln -s '.implode(' ', $pathsToLink).' '.$this->installPath, $output, $resultCode);
    }

    /**
     * Create the wp-config.php file.
     */
    public function createWpConfigFile(): void
    {
        // TODO: Error handling
        app(WpCli::class)->run('config create --dbname=localhost --dbuser=unused --skip-check --insecure --path='.$this->installPath);
    }

    protected function installPlugins()
    {
        if (is_null($this->option('plugins'))) {
            return;
        }

        // TODO: Validate

        $this->info('Installing plugins');
        app(WpCli::class)->run('plugin install --activate '.str_replace(',', ' ', $this->option('plugins')).' --path='.$this->installPath);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(SiteIndex $index)
    {
        $this->validateArguments();

        $this->requestedVersion = $this->getVersionOption();
        $this->installPath = getRealPath($this->getInstallPathOption());
        $this->hostname = $this->getHostnameOption();
        $this->port = $this->getPortOption();

        $this->info('Installing to '.$this->installPath);

        // CHECK NAME AND PATH DON'T ALREADY EXIST
        // Check for an existing directory
        if (File::isDirectory($this->installPath)) {
            $this->error('Directory already exists: '.$this->installPath);

            return;
        }

        // Check for an existing site in the index
        if ($index->exists($this->argument('name'))) {
            $this->error('Site already exists: '.$this->argument('name'));

            return;
        }

        $this->info('Adding site: '.$this->argument('name'));

        // Make the directory
        File::ensureDirectoryExists($this->installPath);

        // Get the actual version number
        $this->actualVersion = app(WpCoreVersion::class)->calculateActualVersionNumber($this->requestedVersion);

        // Link the core files to the sites directory
        $this->linkCoreFiles();

        // or wp config create
        $this->createWpConfigFile();

        // make wp-content directory
        File::ensureDirectoryExists($this->installPath.'/wp-content');
        // make wp-content/plugins
        File::ensureDirectoryExists($this->installPath.'/wp-content/plugins');
        // make wp-content/themes
        File::ensureDirectoryExists($this->installPath.'/wp-content/themes');

        $this->installSQLitePlugin();

        // copy in a theme?
        // TODO: Make this a method of the CoreVersion service
        $themeDirs = File::directories(app(UserStorage::class)->getStoragePathForDirectory('wordpress/'.$this->actualVersion.'/wp-content/themes'));
        $this->info('Copying default themes');

        foreach ($themeDirs as $dir) {
            File::copyDirectory(
                $dir,
                $this->installPath.'/wp-content/themes/'.Str::afterLast($dir, '/')
            );
        }

        // Could add --locale
        app(WpCli::class)->run('core install --url="http://'.$this->hostname.':'.$this->port.'" --title="'.$this->argument('name').'" --admin_user=admin --admin_password=admin --admin_email=admin@example.com --skip-email --path='.$this->installPath);

        $this->installPlugins();

        $installRouterResult = $this->installRouterScript($this->installPath);

        // TODO: Add actual version installed
        $index->add(
            $this->argument('name'),
            $this->installPath,
            $this->requestedVersion,
            $this->actualVersion,
            $this->hostname,
            $this->port,
        );

        $this->info("Starting site on http://{$this->hostname}:{$this->port} - press Ctrl+C to stop - wp-admin login is admin/admin");

        // Note that this is not Laravel 10 yet so we don't have the Process Facade and are using the Symfony Process component directly
        // TODO: Error check on startup
        Process::fromShellCommandline("php -S \"$this->hostname:$this->port\" $this->installPath/router.php", $this->installPath, timeout: null)->run();
    }
}
