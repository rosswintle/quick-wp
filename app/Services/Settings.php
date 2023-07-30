<?php

namespace App\Services;

use App\Site;
use Illuminate\Console\Concerns\InteractsWithIO;
use Symfony\Component\Console\Output\ConsoleOutput;
use Spatie\Valuestore\Valuestore;

class Settings
{
    // Required for console output
    use InteractsWithIO;

    const FILENAME = 'sites.json';

    protected Valuestore $store;

    /**
     * Constructor - initializes the valueµstore.
     */
    public function __construct()
    {
        $filename = config('quickwp.userDirectory') . DIRECTORY_SEPARATOR . self::FILENAME;
        $this->store = Valuestore::make($filename);
    }

    /**
     * Initialises the settings store.
     *
     * @return void
     */
    public function init() : void
    {

    }

    /**
     * Returns an array of all the sites in the index.
     */
    public function all() : array
    {
        return $this->store->all();
    }

    /**
     * Returns a setting by name.
     */
    public function get(string $name, $default=null) : mixed
    {
        return $this->store->get($name, $default);
    }

    /**
     * Check if a site exists in the index.
     */
    public function has(string $name) : bool
    {
        return $this->store->has($name);
    }

    /**
     * Set a setting.
     */
    public function set(string $name, mixed $value) : void
    {
        $this->store->put($name, $value);
    }
}
