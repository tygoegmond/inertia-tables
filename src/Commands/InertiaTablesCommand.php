<?php

namespace Egmond\InertiaTables\Commands;

use Illuminate\Console\Command;

class InertiaTablesCommand extends Command
{
    public $signature = 'inertia-tables';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
