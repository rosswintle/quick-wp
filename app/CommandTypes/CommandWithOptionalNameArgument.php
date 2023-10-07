<?php

namespace App\CommandTypes;

use App\Services\SiteIndex;
use App\Site;
use Illuminate\Console\Command;
use function Laravel\Prompts\search;

class CommandWithOptionalNameArgument extends Command
{
    /**
     * Gets the 'name' argument. If there is none it prompts the user for one.
     *
     * Exits if there are no sites in the index.
     *
     * @param SiteIndex $index
     * @param string $label
     * @return Site
     */
    public function getSiteFromNameArgument(SiteIndex $index, string $label) : Site
    {
        if ($this->argument('name')) {
            $siteName = $this->argument('name');
        } else {
            $sites = $index->all();

            if ($sites->isEmpty()) {
                $this->error("No sites available");
                exit;
            }

            $name = search(
                label: $label,
                options: fn(string $value) =>
                    $sites
                        ->filter(fn($site) => str_starts_with($site->name, $value))
                        ->keyBy('name')
                        ->map(fn($site) => $site->name)
                        ->toArray()
            );
            if ($name) {
                $siteName = $name;
            } else {
                exit;
            }
        }

        // Check for an existing site in the index
        if (! $index->exists($siteName)) {
            $this->error("Site does not exist: " . $siteName);
            exit;
        }

        return $index->get($siteName);
    }
}
