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
    protected $signature = 'add {name}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Creates a site';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(SiteIndex $index)
    {
        $this->info("Adding site: " . $this->argument('name'));
        $index->add($this->argument('name'));

        app(WpCoreVersion::class)->getPath('6.2.2');
    }
}
