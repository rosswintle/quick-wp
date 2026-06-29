<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

use function Laravel\Prompts\info;

trait InstallsRouterScript
{
    const ROUTER_TEMPLATE_FILE = 'router-template.php';

    const ROUTER_DESTINATION_FILE = 'router.php';

    public function getRouterSourcePath(): string
    {
        return app_path(self::ROUTER_TEMPLATE_FILE);
    }

    public function getRouterDestinationPath(string $installPath): string
    {
        static $path = null;

        if (is_null($path)) {
            $path = Str::finish($installPath, '/').self::ROUTER_DESTINATION_FILE;
        }

        return $path;
    }

    public function installRouterScript(string $installPath): bool
    {
        // copy the router.php in
        File::copy(
            $this->getRouterSourcePath(),
            $this->getRouterDestinationPath($installPath)
        );

        // TODO: Handle error cases
        return true;
    }

    public function doesRouterScriptExist(string $installPath): bool
    {
        return File::exists($this->getRouterDestinationPath($installPath));
    }

    /**
     * Assumes both files are known to exist.
     */
    public function doesRouterScriptNeedUpdate(string $installPath): bool
    {
        $sourceHash = md5_file($this->getRouterSourcePath());
        $destHash = md5_file($this->getRouterDestinationPath($installPath));

        // TODO: Handle sourceHash === false
        return $sourceHash !== $destHash;
    }

    /**
     * Install or updates the router script. Returns true if an up-to-date
     * router script now exists.
     */
    public function maybeUpdateRouterScript(string $installPath): bool
    {
        if (! $this->doesRouterScriptExist($installPath)) {
            info('Installing router script');
            $this->installRouterScript($installPath);

            return true;
        }

        if ($this->doesRouterScriptNeedUpdate($installPath)) {
            info('Updating router script');
            $this->installRouterScript($installPath);

            return true;
        }

        // Return success as router script correctly exists.
        return true;
    }
}
