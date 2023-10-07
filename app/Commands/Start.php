<?php

namespace App\Commands;

use App\Services\SiteIndex;
use App\Traits\HasOptionalNameArgumentWithBackupSelection;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class Start extends Command
{
    use HasOptionalNameArgumentWithBackupSelection;

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
     * @return mixed
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

        $this->info("Starting site on http://$site->hostname:$site->port - press Ctrl+C to stop");

        // Note that this is not Laravel 10 yet so we don't have the Process Facade and are using the Symfony Process component directly
        Process::fromShellCommandline("php -S $site->hostname:$site->port $site->path/router.php", $site->path, timeout: null)->run();
    }
}
