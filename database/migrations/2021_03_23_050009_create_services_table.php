<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->references('id')->on('customers');
            $table->foreignId('vehicle_id')->references('id')->on('vehicles');
            $table->foreignId('workshop_id')->references('id')->on('companies');
            $table->foreignId('service_type_id')->references('id')->on('service_types');
            $table->dateTime('appointment_date')->nullable();
            $table->string('status');
            $table->integer('rescheduled_count')->default(0);
            $table->string('remarks')->nullable();
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
        Schema::dropIfExists('services');
    }
}
