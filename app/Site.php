<?php

namespace App;

use Illuminate\Support\Facades\File;

class Site
{
    public string $name;

    public string $path;

    public string $requestedVersion;

    public string $actualVersion;

    public function __construct(string $name, string $path, string $requestedVersion, string $actualVersion)
    {
        $this->name = $name;
        $this->path = $path;
        $this->requestedVersion = $requestedVersion;
        $this->actualVersion = $actualVersion;
    }

    public function toArray() : array
    {
        return [
            'name' => $this->name,
            'path' => $this->path,
            'requestedVersion' => $this->requestedVersion,
            'actualVersion' => $this->actualVersion,
        ];
    }

    public function pathExists() : bool
    {
        return File::exists($this->path);
    }
}
