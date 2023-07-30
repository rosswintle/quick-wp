<?php

namespace App\Commands;

use App\Services\SiteIndex;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class Start extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'start {name}';

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
        // Check for an existing site in the index
        if (! $index->exists($this->argument('name'))) {
            $this->error("Site does not exist: " . $this->argument('name'));
            return;
        }

        $site = $index->get($this->argument('name'));

        // Check that the path exists
        if (! $site->pathExists()) {
            $this->error("Site path does not exist: " . $site->path);
            // TODO: Delete the site from the index
            return;
        }

        $this->info("Starting site on http://localhost:8001 - press Ctrl+C to stop");

        // Note that this is not Laravel 10 yet so we don't have the Process Facade and are using the Symfony Process component directly
        Process::fromShellCommandline("php -S localhost:8001 $site->path/router.php", $site->path, timeout: null)->run();
    }
}