<?php

namespace App\Commands;

class Delete extends Remove
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'delete {name? : The name of the site to remove}';

    protected $hidden = true;
}
