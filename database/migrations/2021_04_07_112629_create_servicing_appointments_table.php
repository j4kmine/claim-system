<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicingAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicing_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicing_slot_id')->references('id')->on('servicing_slots');
            $table->foreignId('service_id')->references('id')->on('services');
            $table->date('appointment_date');
            $table->time('time_start');
            $table->integer('interval');
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
        Schema::dropIfExists('servicing_appointments');
    }
}
