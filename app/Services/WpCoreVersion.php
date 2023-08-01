<?php
// Current release: https://api.wordpress.org/core/stable-check/1.0/
// https://api.wordpress.org/core/version-check/1.7/?channel=development
// https://api.wordpress.org/core/version-check/1.7/?channel=beta
// https://api.wordpress.org/core/version-check/1.7/?channel=rc

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
    public function getPath(string $requestedVersion, string $actualVersion) : string
    {
        if (! $this->isStored($actualVersion)) {
            $this->fetchAndStore($requestedVersion, $actualVersion);
        }
        return $this->pathTo($actualVersion);
    }

    /**
     * Check if a WordPress Core Version is stored.
     */
    protected function isStored(string $version) : bool
    {
        return File::exists($this->pathTo($version));
    }

    /**
     * Calculate the actual version number for a version.
     *
     * There is code in https://github.com/afragen/wordpress-beta-tester/ that may be useful here.
     */
    public function calculateActualVersionNumber(string $version) : string
    {
        if ($version === 'latest') {
            // I'm not sure if "stable" is a valid channel, but we get the latest stable release by default
            $version = $this->getVersionNumberFromChannel('stable');
        }
        if ($version === 'nightly') {
            $version = $this->getVersionNumberFromChannel('development');
        }
        if ($version === 'rc') {
            $version = $this->getVersionNumberFromChannel('rc');
        }
        if ($version === 'beta') {
            $version = $this->getVersionNumberFromChannel('beta');
        }
        return $version;
    }

    /**
     * Get a version number from a given channel of releases.
     */
    protected function getVersionNumberFromChannel(string $stream) : string
    {
        $response = Http::get("https://api.wordpress.org/core/version-check/1.7/?channel={$stream}");
        $version = $response->json()['offers'][0]['version'];
        return $version;
    }

    /**
     * Fetch and store a WordPress Core Version.
     */
    protected function fetchAndStore(string $requestedVersion, string $actualVersion) : void
    {
        $this->info("Fetching WordPress Core Version {$requestedVersion} (actually: $actualVersion)");
        // nightly versions aren't recognised by wp-cli, so we need to assume this is nightly and request that!
        $versionToGet = $requestedVersion === 'nightly' ? 'nightly' : $actualVersion;
        app(WpCli::class)->run("core download --version={$versionToGet} --path={$this->pathTo($actualVersion)} --force");
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
