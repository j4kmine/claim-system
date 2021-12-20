<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Document;
use App\Models\Warranty;

class ImportDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import old CarFren data';

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
        $customers = $this->csvToArray('/var/www/html/carfren/import/customers.csv');
        foreach($customers as $customer){
            
        }
        print_r($customers);

        $vehicles = $this->csvToArray('/var/www/html/carfren/import/orders.csv');
        foreach($vehicles as $vehicle){
            
        }

        $orders = $this->csvToArray('/var/www/html/carfren/import/orders.csv');
        foreach($orders as $order){
            if($order['type'] == 'Order::Warranty'){
                /*
                Warranty::create([

                ]);*/
            }
        }
    }

    function csvToArray($filename = '', $delimiter = ',')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== false)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
            {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }
}
