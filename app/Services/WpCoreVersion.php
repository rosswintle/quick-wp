<?php

namespace App\Services;

use App\Services\WpCli;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Concerns\InteractsWithIO;
use Symfony\Component\Console\Output\ConsoleOutput;

class WpCoreVersion
{
    const DIRECTORY = 'wordpress';

    // Required for console output
    use InteractsWithIO;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->output = new ConsoleOutput();
    }

    /**
     * Initialize the service.
     */
    public function init()
    {
        Storage::makeDirectory('wordpress');
    }

    /**
     * Get the path for a WordPress Core Version install.
     *
     * Will fetch and store the version if it doesn't exist.
     */
    public function getPath(string $version) : string
    {
        if (! $this->isStored($version)) {
            $this->fetchAndStore($version);
        }
        return $this->pathTo($version);
    }

    /**
     * Check if a WordPress Core Version is stored. Note that 'nightly' is never cached.
     */
    protected function isStored(string $version) : bool
    {
        // TODO: Store nightly versions and check when update is needed.
        if ($version === 'nightly') {
            return false;
        }
        return Storage::exists(self::DIRECTORY . "/$version");
    }

    /**
     * Fetch and store a WordPress Core Version.
     */
    protected function fetchAndStore(string $version) : void
    {
        $this->info("Fetching WordPress Core Version {$version}");
        // --force is needed to make sure we have nightlies
        app(WpCli::class)->run("core download --version={$version} --path={$this->pathTo($version)} --force");
    }

    /**
     * Get the path for a WordPress Core Version.
     */
    protected function pathTo(string $version) : string
    {
        // TODO: Make the wordpress directory if needed
        return Storage::path(self::DIRECTORY . "/$version");
    }
}
