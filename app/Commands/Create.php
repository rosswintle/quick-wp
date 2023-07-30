<?php

namespace App\Commands;

class Create extends Add
{
    protected $hidden = true;

    public function configure()
    {
        parent::configure();
        $this->setName('create');
    }
}
