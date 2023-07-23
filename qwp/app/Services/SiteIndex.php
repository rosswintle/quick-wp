<?php

namespace App\Services;

use App\Site;
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
        if (!Storage::exists('sites.json')) {
            Storage::put('sites.json', json_encode([]));
        }

        $this->sites = $this->fromArray(json_decode(Storage::get('sites.json'), associative: false));

        // Set up the console output
        $this->output = new ConsoleOutput();
    }

    protected function fromArray(array $sitesArray) : Collection
    {
        return $this->sites = collect($sitesArray)->map(function ($site) {
            return new Site($site->name, $site->path);
        });
    }

    /**
     * Returns an array of all the sites in the index.
     */
    public function all() : Collection
    {
        return $this->sites;
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
    public function add(string $name) : void
    {
        if ($this->exists($name)) {
            $this->warn("Site already exists");
            return;
        }

        $this->sites[] = new Site($name, 'some/path/' . $name);

        Storage::put('sites.json', json_encode($this->sites));
    }
}
