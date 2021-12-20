<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMotorDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('motor_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('motor_id');
            $table->foreign('motor_id')->references('id')->on('motors');
            $table->string('name');
            $table->string('url');
            $table->string('type'); 
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
        Schema::dropIfExists('motor_documents');
    }
}
