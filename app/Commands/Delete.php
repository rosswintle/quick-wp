<?php

namespace App\Commands;

class Delete extends Remove
{
    protected $hidden = true;

    public function configure()
    {
        parent::configure();
        $this->setName('delete');
    }
}
