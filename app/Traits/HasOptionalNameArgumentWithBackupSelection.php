<?php
namespace App\Traits;

use App\Services\Settings;
use App\Services\SiteIndex;
use App\Site;
use Illuminate\Support\Str;

use function Laravel\Prompts\select;

trait HasOptionalNameArgumentWithBackupSelection
{
    /**
     * Gets the 'name' argument. If there is none it prompts the user for one.
     *
     * Exits if there are no sites in the index.
     *
     * @param SiteIndex $index
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

            $name = select(
                label: $label,
                options: $sites
                    ->keyBy('name')
                    ->map(fn($site) => $site->name)
                    ->toArray(),
                scroll: 10
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
