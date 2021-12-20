<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicingReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicing_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicing_id')->references('id')->on('services');
            $table->double('total_amount')->default(0);
            $table->string('workshop_remarks')->nullable();
            $table->string('all_cars_remarks')->nullable();
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
        Schema::dropIfExists('servicing_reports');
    }
}
