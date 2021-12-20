<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarrantyPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warranty_prices', function (Blueprint $table) {
            $table->id();
            $table->string('make');
            $table->string('model');
            $table->string('category');
            $table->integer('capacity');
            $table->string('type');
            $table->string('fuel');
            $table->integer('price');
            $table->integer('max_claim');
            $table->integer('mileage_coverage');
            $table->decimal('warranty_duration', 8, 2);
            $table->string('status');
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
        Schema::dropIfExists('warranty_prices');
    }
}
