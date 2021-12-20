<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->references('id')->on('customers');
            $table->date('date_of_accident');
            $table->string('location_of_accident');
            $table->string('weather_road_condition');
            $table->string('reporting_type');
            $table->integer('number_of_passengers');
            $table->boolean('is_video_captured');
            $table->string('purpose_of_use');
            $table->text('details')->nullable();
            $table->string('vehicle_car_plate');
            $table->string('vehicle_make');
            $table->string('vehicle_model');
            $table->string('insurance_company');
            $table->string('certificate_number');
            $table->string('insured_nric');
            $table->string('insured_name');
            $table->string('insured_contact_number');
            $table->boolean('is_visiting_workshop');
            $table->foreignId('workshop_id')->references('id')->on('companies');
            $table->dateTime('workshop_visit_date')->nullable();
            $table->boolean('is_owner_drives');
            $table->string('owner_driver_relationship');

            if (App::runningUnitTests()) {
                $table->foreignId('vehicle_id')->references('id')->on('vehicles');
            }

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reports');
    }
}
