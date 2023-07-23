<?php

namespace App\Commands;

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
    protected $signature = 'add {name} {--wp-version=latest}';

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

        $this->info("Adding site: " . $this->argument('name'));
        $index->add($this->argument('name'));

        app(WpCoreVersion::class)->getPath($this->option('wp-version'));
    }
}
