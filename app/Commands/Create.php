<?php

namespace App\Commands;

class Create extends Add
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'create {name}
    {--wp-version=latest : Version can be a verison number, "latest" or "nightly"}
    {--path= : A path to install to. Defaults to a subdirectory of the current directory or the configured default path. }';

    protected $hidden = true;
}
