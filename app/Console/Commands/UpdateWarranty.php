<?php

namespace App\Console\Commands;

use App\Models\Warranty;
use Illuminate\Console\Command;

class UpdateWarranty extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:warranty';

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
        //search wararnty with 0 duration
        $warranties = Warranty::where('warranty_duration', 0)->where('status', 'completed')->get();

        foreach ($warranties as $key => $value) {
            echo "updating warranty duration " . $value['ci_no'];
            $warranty_duration = $this->calculateDuration(
                $this->normalizeDate($value['start_date']),
                $this->normalizeDate($value['expiry_date'])
            );
            echo " new duration $warranty_duration month, updating.. ";

            $warranty = Warranty::find($value['id']);
            $warranty->warranty_duration = $warranty_duration;
            $warranty->save();

            echo "done update \n";
        }

        return 0;
    }

    private function normalizeDate($date)
    {
        return explode(' ', $date)[0];
    }

    private function calculateDuration($start, $end)
    {
        $ts1 = strtotime($start);
        $ts2 = strtotime($end);

        $year1 = date('Y', $ts1);
        $year2 = date('Y', $ts2);

        $month1 = date('m', $ts1);
        $month2 = date('m', $ts2);

        return (($year2 - $year1) * 12) + ($month2 - $month1);
    }
}
