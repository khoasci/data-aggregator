<?php

namespace App\Console\Commands\Dump;

class DumpReset extends AbstractDumpCommand
{

    protected $signature = 'dump:reset';

    protected $description = 'Clear local/json dumps';

    public function handle()
    {
        $dumpPath = $this->getDumpPath('local/json');

        $this->shell->passthru('rm -rf %s/*', $dumpPath);
    }
}
