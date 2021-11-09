<?php

namespace App\Console\Commands;

use App\Models\Build;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoDismissBuildsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build:auto-dismiss';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically set builds as dismissed';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dismissAfter = config('build.dismiss_after_days', -1);

        if ($dismissAfter <= 0) {
            $this->error('Auto dismissing is not enabled.');
            return 1;
        }

        $interval = Carbon::now()->subDays($dismissAfter);

        Build::query()
            ->where('available_from', '<', $interval->toDateTimeString())
            ->update(['dismissed' => true]);

        return 0;
    }
}
