<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProposersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proposers', function (Blueprint $table) {
            $table->id();
            $table->string('salutation')->nullable();
            $table->string('name')->nullable();
            $table->longText('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('nric_uen')->nullable();
            $table->date('date_of_birth')->nullable();
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
        Schema::dropIfExists('proposers');
    }
}
