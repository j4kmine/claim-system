<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            //
            $table->integer('capacity')->after('mileage')->nullable();
            $table->string('category')->after('capacity')->nullable();
            $table->string('type')->after('nric_uen')->nullable();
            $table->string('fuel')->after('type')->nullable();
            $table->string('engine_no')->after('chassis_no')->nullable();
            $table->date('registration_date')->after('fuel')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicles', function (Blueprint $table) {
            //
            $table->dropColumn('category');
            $table->dropColumn('type');
            $table->dropColumn('fuel');
            $table->dropColumn('engine_no');
            $table->dropColumn('registration_date');
        });
    }
}
