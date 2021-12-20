<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInsurersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('insurers', function (Blueprint $table) {
            $table->id();
            // One surveyor can have many insurer
            // One insurer 1 surveyor
            $table->unsignedBigInteger('insurer_id')->unique();
            $table->foreign('insurer_id')->references('id')->on('companies');
            $table->unsignedBigInteger('surveyor_id');
            $table->foreign('surveyor_id')->references('id')->on('companies');           
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
        Schema::dropIfExists('insurers');
    }
}
