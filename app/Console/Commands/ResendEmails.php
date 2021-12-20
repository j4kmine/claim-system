<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WarrantyDocument;
use App\Models\Warranty;
use Storage;
use Carbon\Carbon;

class ResendEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resend:emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend emails to III Warranty Insurance Team';

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
        $warranties = Warranty::where('status','completed')->get();
        foreach($warranties as $warranty){
            $files = [];
            $warranty_log = WarrantyDocument::where('warranty_id', $warranty->id)->where('type', 'log')->first();
            if($warranty_log != null){
                $log_attachment = [
                    'path' => Storage::disk('s3')->temporaryUrl(substr(parse_url($warranty_log->url)['path'], 1), Carbon::now()->addMinutes(30)),
                    'name' => 'log_card.'.pathinfo($warranty_log->name, PATHINFO_EXTENSION),
                    'mime' => mime_type(basename($warranty_log->url))
                ];
                $files[] = $log_attachment;
            }

            $warranty_form = WarrantyDocument::where('warranty_id', $warranty->id)->where('type', 'form')->first();
            if($warranty_form != null){
                $form_attachment = [
                    'path' => Storage::disk('s3')->temporaryUrl(substr(parse_url($warranty_form->url)['path'], 1), Carbon::now()->addMinutes(30)),
                    'name' => 'salesperson_form.pdf',
                    'mime' => mime_type(basename($warranty_form->url))
                ];
                $files[] = $form_attachment;
            }

            $warranty_ci = WarrantyDocument::where('warranty_id', $warranty->id)->where('type', 'ci')->first();
            if($warranty_ci != null){
                $ci_attachment = [
                    'path' => Storage::disk('s3')->temporaryUrl(substr(parse_url($warranty_ci->url)['path'], 1), Carbon::now()->addMinutes(30)),
                    'name' => 'ci.pdf',
                    'mime' => mime_type(basename($warranty_ci->url))
                ];
                $files[] = $ci_attachment;
            }

            $warranty_assessment = WarrantyDocument::where('warranty_id', $warranty->id)->where('type', 'assessment')->first();
            if($warranty_assessment != null){
                $assessment_attachment = [
                    'path' => Storage::disk('s3')->temporaryUrl(substr(parse_url($warranty_assessment->url)['path'], 1), Carbon::now()->addMinutes(30)),
                    'name' => 'assessment_report.'.pathinfo($warranty_assessment->name, PATHINFO_EXTENSION),
                    'mime' => mime_type(basename($warranty_assessment->url))
                ];
                $files[] = $assessment_attachment;
            }

            email($warranty, 'emails.warranties.approve_warranty_to_insurer', $warranty->ref_no.' – Warranty Approved by AllCars', null, $warranty->insurer_id, $files);
        }
    }
}
