<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBoolToVehiclesTable extends Migration
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
            $table->boolean('off_peak')->nullable()->after('category');
            $table->boolean('modified')->nullable()->after('category');
            $table->longText('modification_remarks')->nullable()->after('category');
            $table->boolean('seating_capacity')->nullable()->after('category');
            $table->string('body_type')->nullable()->after('seating_capacity');
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
            $table->dropColumn('off_peak');
            $table->dropColumn('modified');
            $table->dropColumn('modification_remarks');
            $table->dropColumn('seating_capacity');
            $table->dropColumn('body_type');
        });
    }
}
