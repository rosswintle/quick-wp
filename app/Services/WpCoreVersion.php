<?php

namespace App\Services;

use App\Services\WpCli;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Concerns\InteractsWithIO;
use Symfony\Component\Console\Output\ConsoleOutput;

class WpCoreVersion
{
    const DIRECTORY = 'wordpress';

    // Required for console output
    use InteractsWithIO;

    protected string $coreCachePath = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->output = new ConsoleOutput();
        $this->coreCachePath = Str::of(config('quickwp.userDirectory'))->finish('/') .  self::DIRECTORY;
    }

    /**
     * Initialize the service.
     */
    public function init()
    {
        File::ensureDirectoryExists($this->coreCachePath);
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
        return File::exists($this->pathTo($version));
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
        return $this->coreCachePath . "/$version";
    }
}
