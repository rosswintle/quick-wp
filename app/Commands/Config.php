<?php

namespace App\Commands;

use App\Services\Settings;
use Illuminate\Support\Facades\File;
use LaravelZero\Framework\Commands\Command;

class Config extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'config {--default-path= : The default path to install sites to}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Configure quick-wp';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->handleDefaultPath();
    }

    public function handleDefaultPath()
    {
        $defaultPath = $this->option('default-path');

        if (! $defaultPath) {
            return;
        }

        if (! File::exists($defaultPath)) {
            $create = $this->confirm("$defaultPath does not exist, do you want me to create it?", true);
            if ($create) {
                File::ensureDirectoryExists($defaultPath);
            } else {
                $this->info("OK. I won't set the default path.");
                return;
            }
        }
        app(Settings::class)->set('default-path', $defaultPath);
        $this->info("Default path set to $defaultPath");
    }
}
