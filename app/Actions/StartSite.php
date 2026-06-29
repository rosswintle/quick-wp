<?php

namespace App\Actions;

use App\Site;
use App\Traits\InstallsRouterScript;
use Illuminate\Console\Concerns\InteractsWithIO;
use Illuminate\Support\Facades\Process;

use function Laravel\Prompts\info;

class StartSite
{
    use InstallsRouterScript;
    use InteractsWithIO;

    public function handle(Site $site): void
    {
        // Maybe update router.php?
        $this->maybeUpdateRouterScript($site->path);

        info("Starting site on http://$site->hostname:$site->port - press Ctrl+C to stop");

        Process::forever()
            ->path($site->path)
            ->run("php -S $site->hostname:$site->port $site->path/router.php");
    }
}
