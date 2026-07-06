<?php

namespace App\Traits;

use App\Services\UserStorage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Composer\Semver\Comparator;

trait InstallsSQLitePlugin
{
    const PLUGIN_SLUG = 'sqlite-database-integration';
    const PLUGIN_URL = 'https://downloads.wordpress.org/plugin/sqlite-database-integration.zip';

    public function sqlitePluginStorageDirectory(): string
    {
        return Str::finish(app(UserStorage::class)->getPluginsPath(), '/') . self::PLUGIN_SLUG;
    }

    public function sqlitePluginZipFilePath(): string
    {
        return Str::finish(app(UserStorage::class)->getPluginsPath(), '/') . self::PLUGIN_SLUG . '.zip';
    }

    public function isSQLitePluginInUserStorage(): bool
    {
        $storagePath = app(UserStorage::class)->getPluginsPath();
        return File::isDirectory($storagePath) &&
            File::isDirectory(
                $this->sqlitePluginStorageDirectory()
            );
    }

    public function getStoredSQLitePluginVersion(): string
    {
        $loadFileContent = File::get(Str::finish($this->sqlitePluginStorageDirectory(), '/') . 'load.php');
        // Look for "Version: 2.2.23"
        if (preg_match('/Version: ([0-9\.]+)$/m', $loadFileContent, $matches)) {
            return $matches[1];
        }

        return '0.0.0';
    }

    public function getSQLitePluginUpdateVersion(): string
    {
        $response = Http::withHeaders(
                [
                    'User-Agent' => '',
                ]
            )
            ->acceptJson()
            ->get('https://api.wordpress.org/plugins/info/1.0/' . self::PLUGIN_SLUG . '.json');

        // TODO: Maybe cache this for a day?
        return $response->json('version', '0.0.0');
    }

    public function maybeUpdateStoredSQLitePlugin(): bool
    {
        $currentVersion = $this->getStoredSQLitePluginVersion();

        $updateVersion = $this->getSQLitePluginUpdateVersion();

        if (Comparator::lessThanOrEqualTo($updateVersion, $currentVersion)) {
            return false;
        }

        File::deleteDirectory($this->sqlitePluginStorageDirectory());

        $this->fetchSQLitePlugin();
        return true;
    }

    /**
     * Fetches the most recent version of the SQLite plugin.
     *
     * @return void
     */
    public function fetchSQLitePlugin(): bool
    {
        $this->info('Fetching SQLite plugin');
        $response = Http::withOptions([
            'sink' => $this->sqlitePluginZipFilePath(),
        ])
            ->accept('*/*')
            ->withHeaders(
                [
                    'User-Agent' => '',
                ]
            )
            ->get(self::PLUGIN_URL);

        if (!$response->ok()) {
            $this->error('Failed to download SQLite plugin');
            exit();
        }

        // Unzip the plugin
        $this->info('Unzipping SQLite plugin');
        $zip = new \ZipArchive;
        $zip->open($this->sqlitePluginZipFilePath());
        $zip->extractTo(app(UserStorage::class)->getPluginsPath());
        return true;
    }

    public function installSQLitePlugin(): void
    {
        // NEW CODE
        if ($this->isSQLitePluginInUserStorage()) {
            $this->info('Using existing SQLite plugin');
            $this->maybeUpdateStoredSQLitePlugin();
        } else {
            $this->fetchSQLitePlugin();
        }

        // Copy to the site
        File::copyDirectory(
            $this->sqlitePluginStorageDirectory(),
            Str::finish($this->installPath, '/') . 'wp-content/plugins/sqlite-database-integration');

        File::copy(
            Str::finish($this->sqlitePluginStorageDirectory(), '/') . 'db.copy',
            Str::finish($this->installPath, '/') . 'wp-content/db.php');

        // from https://github.com/WordPress/sqlite-database-integration/issues/7#issuecomment-1563465590
        $dbPhp = File::get(Str::finish($this->installPath, '/') . 'wp-content/db.php');
        // Replace the placeholders with the correct values
        $dbPhp = str_replace(
            [
                '{SQLITE_IMPLEMENTATION_FOLDER_PATH}',
                '{SQLITE_PLUGIN}',
            ],
            [
                Str::finish($this->installPath, '/') . 'wp-content/plugins/sqlite-database-integration',
                'sqlite-database-integration/load.php',
            ],
            $dbPhp
        );
        file_put_contents(Str::finish($this->installPath, '/') . 'wp-content/db.php', $dbPhp);

        File::makeDirectory(Str::finish($this->installPath, '/') . 'wp-content/database');
        File::put(
            Str::finish($this->installPath, '/') . 'wp-content/database/.ht.sqlite',
            ''
        );

    }
}
