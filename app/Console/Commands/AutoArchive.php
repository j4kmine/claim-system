<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Claim;
use App\Models\ClaimActionLog;
use Carbon\Carbon;

class AutoArchive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'claims:archive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto archive after 90 days';

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
        $claims = Claim::where('status', 'draft')->where('created_at', '<=', Carbon::now()->subDays(90))->get();
        foreach($claims as $claim){
            $claim->status = 'archive';
            $claim->save();
            ClaimActionLog::create([
                'claim_id' => $claim->id,
                'log' => 'Claim ' . $claim->ref_no . ' auto archived after 90 days from draft.',
                'status' => $claim->status,
                'user_id' => null
            ]);
        }
    }
}
