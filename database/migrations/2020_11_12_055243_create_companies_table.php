<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('acra');
            $table->string('address');
            $table->string('contact_no');
            $table->string('contact_person');
            $table->string('contact_email')->nullable();
            $table->longText('description')->nullable();
            $table->string('type');
            $table->string('status');
            $table->unique(['name', 'type']);
            $table->unique(['code', 'type']);
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
        Schema::dropIfExists('companies');
    }
}
