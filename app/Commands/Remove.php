<?php

namespace App\Commands;

use App\Services\SiteIndex;
use App\Traits\GetsInstallPath;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;

class Remove extends Command
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
     * @return mixed
     */
    public function handle(SiteIndex $index)
    {
        $siteName = $this->argument('name');

        // Provide a selectable list if the name is not provided
        if (! $this->argument('name')) {
            $sites = $index->all();

            if ($sites->isEmpty()) {
                $this->error("No sites to remove");
                return;
            }

            $name = select(
                'Which site do you want to remove?',
                $sites->map(fn ($site) => $site->name)->toArray()
            );
            if ($name) {
                $siteName = $name;
            }
        }

        if (! $index->exists($siteName)) {
            $this->error("Site does not exist: " . $siteName);
            return;
        }

        $site = $index->get($siteName);

        $this->warn("This will delete the directory $site->path");
        if (! confirm("Are you sure you want to delete site '" . $site->name, false)) {
            $this->info("OK. I won't delete the site.");
            return;
        }

        $this->info("Removing site: " . $site->name);

        File::deleteDirectory($site->path);

        $index->remove($site->name);
    }
}
