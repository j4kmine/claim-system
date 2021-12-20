<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class AddNullableColumnsInReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('vehicle_car_plate');

            $table->date('date_of_accident')->nullable()->change();
            $table->string('location_of_accident')->nullable()->change();
            $table->string('weather_road_condition')->nullable()->change();
            $table->string('reporting_type')->nullable()->change();
            $table->integer('number_of_passengers')->nullable()->change();
            $table->boolean('is_video_captured')->nullable()->change();
            $table->string('purpose_of_use')->nullable()->change();
            $table->string('insurance_company')->nullable()->change();
            $table->string('certificate_number')->nullable()->change();
            $table->string('insured_nric')->nullable()->change();
            $table->string('insured_name')->nullable()->change();
            $table->string('insured_contact_number')->nullable()->change();
            $table->boolean('is_visiting_workshop')->nullable()->change();
            $table->foreignId('workshop_id')->nullable()->change();
            $table->boolean('is_owner_drives')->nullable()->change();
            $table->string('owner_driver_relationship')->nullable()->change();

            if (!App::runningUnitTests()) {
                $table->foreignId('vehicle_id')->after('customer_id')->references('id')->on('vehicles')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('vehicle_car_plate');
        });
    }
}
