<?php

namespace App\Commands;

use App\CommandTypes\CommandWithOptionalNameArgument;
use App\Services\SiteIndex;
use App\Traits\GetsInstallPath;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;

class Remove extends CommandWithOptionalNameArgument
{
    use GetsInstallPath;

    /**
     * The signature of the command.
     *
     * If this changes be sure to update the delete command too.
     *
     * @var string
     */
    protected $signature = 'remove {name? : The name of the site to remove}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Deletes a site';

    /**
     * The path to install to - will default to a subdirectory of the current
     * directory using the site name.
     */
    protected string $installPath;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(SiteIndex $index)
    {
        $site = $this->getSiteFromNameArgument($index, 'Which site do you want to remove?');

        $this->warn("This will delete the directory $site->path");
        if (! confirm("Are you sure you want to delete site '$site->name'", false)) {
            $this->info("OK. I won't delete the site.");
            return;
        }

        $this->info("Removing site: " . $site->name);

        File::deleteDirectory($site->path);

        $index->remove($site->name);
    }
}
