<?php

namespace App\Services;

use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Str;
use Laravel\Prompts\Output\ConsoleOutput;

class UserStorage
{
    // Required for console output
    use InteractsWithIO;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Set up the console output
        $this->output = new ConsoleOutput;
    }

    public function getStoragePathForDirectory(string $directory): string
    {
        return Str::of(config('quickwp.userDirectory'))->finish('/').$directory;
    }

    public function getPluginsPath() : string {
        return $this->getStoragePathForDirectory('plugins');
    }

}
