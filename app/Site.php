<?php

namespace App;

use Illuminate\Support\Facades\File;

class Site
{
    public string $name;

    public string $path;

    public string $version;

    public function __construct(string $name, string $path, string $version)
    {
        $this->name = $name;
        $this->path = $path;
        $this->version = $version;
    }

    public function toArray() : array
    {
        return [
            'name' => $this->name,
            'path' => $this->path,
            'version' => $this->version,
        ];
    }

    public function pathExists() : bool
    {
        return File::exists($this->path);
    }
}
