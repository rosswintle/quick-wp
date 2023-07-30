<?php

namespace App\Commands;

class SummaryCommand extends \NunoMaduro\LaravelConsoleSummary\SummaryCommand
{
    /**
     * The supported format. This is required for overriding the "list" command.
     */
    protected const FORMAT = 'txt';

    public function configure()
    {
        parent::configure();

        $this->setName('summary');
    }
}
