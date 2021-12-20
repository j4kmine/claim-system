<?php

namespace App\Console\Commands;

use App\Models\Warranty;
use App\Notifications\ExpiryReminder;
use Illuminate\Console\Command;

class CheckExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:expiry';

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
     * Check Expiry Date for:
     * Warranty & Insurance
     * Give notification if going to expire in 30 days
     */
    public function handle()
    {
        $date = now()->addDays(30);

        /**
         * Check expiry date for Warranty
         */
        $warranties = Warranty::whereDate('expiry_date', $date)->get();
        foreach($warranties as $warranty){
            $warranty->customer->notify(new ExpiryReminder($warranty, 'expiry_date'));
        }
        return 0;
    }
}
