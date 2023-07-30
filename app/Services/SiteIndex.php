<?php

namespace App\Services;

use App\Site;
use App\Services\Settings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Concerns\InteractsWithIO;
use Symfony\Component\Console\Output\ConsoleOutput;

class SiteIndex
{
    // Required for console output
    use InteractsWithIO;

    protected Collection $sites;

    /**
     * Constructor - loads the site index from storage.
     */
    public function __construct()
    {
        // Set up the console output
        $this->output = new ConsoleOutput();
    }

    public function init() : void
    {
        $settings = app(Settings::class);
        $this->sites = $this->fromArray($settings->get('sites', []));
    }

    protected function fromArray(array $sitesArray) : Collection
    {
        return $this->sites = collect($sitesArray)->map(function ($site) {
            return new Site($site['name'], $site['path'], $site['version']);
        });
    }

    /**
     * Returns an array of all the sites in the index.
     */
    public function all() : Collection
    {
        return $this->sites;
    }

    public function allAsArray() : array
    {
        return $this->sites->map(fn ($site) => $site->toArray())->toArray();
    }

    /**
     * Returns a site by name.
     */
    public function get(string $name) : Site|null
    {
        return $this->sites->firstWhere('name', $name);
    }

    /**
     * Check if a site exists in the index.
     */
    public function exists(string $name) : bool
    {
        return ! empty($this->get($name));
    }

    /**
     * Adds a site to the index
     */
    public function add(string $name, string $path, string $version) : void
    {
        if ($this->exists($name)) {
            $this->warn("Site already exists");
            return;
        }

        $this->sites[] = new Site($name, $path, $version);

        app(Settings::class)->set('sites', $this->sites);
    }
}
