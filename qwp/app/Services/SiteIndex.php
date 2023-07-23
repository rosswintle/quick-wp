<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class SiteIndex
{
    protected array $sites;

    public function __construct()
    {
        if (!Storage::exists('sites.json')) {
            Storage::put('sites.json', json_encode([]));
        }

        $this->sites = json_decode(Storage::get('sites.json'), true);
    }
}
