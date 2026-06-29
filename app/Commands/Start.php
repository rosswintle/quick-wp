<?php

namespace App\Commands;

use App\Actions\StartSite;
use App\CommandTypes\CommandWithOptionalNameArgument;
use App\Services\SiteIndex;

class Start extends CommandWithOptionalNameArgument
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'start {name?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Starts the local web server for a site';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(SiteIndex $index)
    {
        // Provide a selectable list if the name is not provided
        $site = $this->getSiteFromNameArgument($index, 'Which site do you want to start?');

        // Check that the path exists
        if (! $site->pathExists()) {
            $this->error("Site path does not exist: " . $site->path);
            // TODO: Delete the site from the index
            return;
        }

        (new StartSite)->handle($site);
    }
}
