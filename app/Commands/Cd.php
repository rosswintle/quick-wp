<?php

namespace App\Commands;

use App\Services\SiteIndex;
use App\Traits\HasOptionalNameArgumentWithBackupSelection;
use LaravelZero\Framework\Commands\Command;

class Cd extends Command
{
    use HasOptionalNameArgumentWithBackupSelection;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'cd {name?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Gets the directory for a site - I can\'t change directory for you but you can use this to do it yourself.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(SiteIndex $index)
    {

        $site = $this->getSiteFromNameArgument($index, 'Select a site to get the directory for');

        $this->info($site->path);
    }
}
