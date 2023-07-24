<?php

namespace App\Commands;

use App\Services\SiteIndex;
use LaravelZero\Framework\Commands\Command;

class Show extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'show';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Shows a list of all of your sites';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(SiteIndex $index)
    {
        $this->info("List of sites");
        $this->table(['Name', 'Path', 'Version'], $index->allAsArray());
    }
}
