<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Console\Concerns\InteractsWithIO;
use Symfony\Component\Console\Output\ConsoleOutput;

class WpCli
{
    // Required for console output
    use InteractsWithIO;

    /**
     * The WP-CLI constructor checks if WP-CLI is installed and, if it isn't
     * fetches and stores it.
     *
     * @return void
     */
    public function __construct()
    {
        $this->output = new ConsoleOutput();
    }

    public function init()
    {
        if (! $this->isInstalled()) {
            $this->install();
        }
    }

    /**
     * Check if WP-CLI is installed.
     *
     * @return bool
     */
    public function isInstalled() : bool
    {
        return File::exists(config('quickwp.userDirectory') . '/wp-cli.phar');
    }

    /**
     * Install WP-CLI.
     *
     * @return void
     */
    public function install() : void
    {
        $this->info('Installing WP-CLI');
        Http::withOptions(['sink' => config('quickwp.userDirectory') . '/wp-cli.phar'])
            ->get('https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar');
    }

    /**
     * Run a WP-CLI command.
     */
    public function run(string $command) : void
    {
        $this->info("Running WP-CLI command: {$command}");
        exec('php ' . config('quickwp.userDirectory') . '/wp-cli.phar' .  " " . $command, $output, $resultCode);
        echo implode("\n", $output);
        echo "\n";
    }
}
