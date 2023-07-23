<?php

namespace App\Commands;

use App\Services\WpCli;
use App\Services\SiteIndex;
use App\Services\WpCoreVersion;
use LaravelZero\Framework\Commands\Command;

class Add extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'add {name} {--wp-version=latest} {--path=}';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(SiteIndex $index)
    {
        if ($this->option('wp-version') === 'latest') {
            $this->version = app(WpCoreVersion::class)->getLatestVersion();
        } else {
            $this->version = $this->option('wp-version');
        }

        $this->installPath = $this->option('path') ?? ( getcwd() . '/' . $this->argument('name') );

        // CHECK NAME AND PATH DON'T ALREADY EXIST
        // Check for an existing directory
        if (file_exists($this->installPath)) {
            $this->error("Directory already exists: " . $this->installPath);
            return;
        }

        $this->info("Adding site: " . $this->argument('name'));

        // Make the directory
        mkdir($this->installPath);

        $coreFilesPath = app(WpCoreVersion::class)->getPath($this->option('wp-version'));


        // Link the core files to the sites directory
        $filesToLink = [
            '/wp-admin',
            '/wp-includes',
            '/wp-*.php',
            '/index.php',
            '/xmlrpc.php',
        ];

        $pathsToLink = array_map(
            fn ($file) => $coreFilesPath . $file,
            $filesToLink
        );

        exec('ln -s ' . implode(' ', $pathsToLink) . ' ' . $this->installPath, $output, $resultCode);

        // copy config (have to copy this from source, not the symlink)
        // or wp config create
        app(WpCli::class)->run('config create --dbname=localhost --dbuser=unused --skip-check --insecure --path=' . $this->installPath);

        // make wp-content directory
        mkdir($this->installPath . '/wp-content');
        // make wp-content/plugins
        mkdir($this->installPath . '/wp-content/plugins');
        // make wp-content/themes
        mkdir($this->installPath . '/wp-content/themes');

        // Check SQLite plugin exists and get it if required

        // copy sqlite plugin
        // cp -r $QWP_DIR/sqlite-database-integration $INSTALL_DIR/wp-content/plugins

        // copy plugins/sqlite-database-integration/db.copy to wp-content/db.php'
        // cp -r $QWP_DIR/sqlite-database-integration/db.copy $INSTALL_DIR/wp-content/db.php

        // copy in a theme?
        // cp -r $QWP_DIR/$VERSION_REQUESTED/wp-content/themes/* $INSTALL_DIR/wp-content/themes/

        // from https://github.com/WordPress/sqlite-database-integration/issues/7#issuecomment-1563465590
        // sed -i '' 's#{SQLITE_IMPLEMENTATION_FOLDER_PATH}#${INSTALL_DIR}/wp-content/plugins/sqlite-database-integration#' ${INSTALL_DIR}/wp-content/db.php
        // sed -i '' 's#{SQLITE_PLUGIN}#sqlite-database-integration/load.php#' ${INSTALL_DIR}/wp-content/db.php
        // mkdir $INSTALL_DIR/wp-content/database && touch $INSTALL_DIR/wp-content/database/.ht.sqlite

        // Could add --locale
        // $WP_CLI core install --url=http://localhost:8001 --title="A New Hope" --admin_user=admin --admin_password=admin --admin_email=admin@example.com --skip-email

        // copy the router.php in
        // cp $SCRIPT_DIR/router.php $INSTALL_DIR

        // php -S localhost:<port> <path>/router.php
        // php -S localhost:8001 $INSTALL_DIR/router.php

        $index->add($this->argument('name'), $this->installPath, $this->version);
    }
}
