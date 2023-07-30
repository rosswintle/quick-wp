<?php
namespace App\Traits;

use App\Services\Settings;
use Illuminate\Support\Str;

trait GetsInstallPath
{
    /**
     * Get the install path option and process it - defaults to a subdirectory of the
     * current directory.
     */
    public function getInstallPathOption() : string
    {
        // Default is to use a subdirectory of the current directory
        $installPath = getcwd() . '/' . $this->argument('name');

        // Use default path setting is it is set
        $settings = app(Settings::class);
        if ($settings->has('default-path')) {
            $installPath = Str::of($settings->get('default-path'))->finish('/') . $this->argument('name');
        }

        // Use CLI-specified path if that is set
        if ($this->option('path')) {
            $installPath = $this->option('path');
        }

        return $installPath;
    }
}
