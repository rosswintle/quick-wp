<?php

namespace App;

use Illuminate\Support\Facades\File;

class Site
{
    const DEFAULT_HOSTNAME = 'localhost';

    const DEFAULT_PORT = 8001;

    public string $name;

    public string $path;

    public string $requestedVersion;

    public string $actualVersion;

    public string $hostname;

    public int $port;

    public function __construct(
        string $name,
        string $path,
        string $requestedVersion,
        string $actualVersion,
        string $hostname = self::DEFAULT_HOSTNAME,
        int $port = self::DEFAULT_PORT)
    {
        $this->name = $name;
        $this->path = $path;
        $this->requestedVersion = $requestedVersion;
        $this->actualVersion = $actualVersion;
        $this->hostname = $hostname;
        $this->port = $port;
    }

    public function toArray() : array
    {
        return [
            'name' => $this->name,
            'path' => $this->path,
            'requestedVersion' => $this->requestedVersion,
            'actualVersion' => $this->actualVersion,
            'hostname' => $this->hostname ?? self::DEFAULT_HOSTNAME,
            'port' => $this->port ?? self::DEFAULT_PORT,
        ];
    }

    public function pathExists() : bool
    {
        return File::exists($this->path);
    }
}
