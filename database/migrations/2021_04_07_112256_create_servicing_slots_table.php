<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicingSlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicing_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->references('id')->on('companies');
            $table->string('day');
            $table->time('time_start');
            $table->time('time_end');
            $table->integer('interval'); // In minutes
            $table->integer('slots_per_interval');
            $table->string('status')->default('active');
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
        Schema::dropIfExists('servicing_slots');
    }
}
