<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Service;
use Carbon\Carbon;

class UpdateServicing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:servicing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        Service::where('status', 'upcoming')->where('appointment_date', '<=', Carbon::now())->update(['status' => 'cancelled']);
        return 0;
    }
}
