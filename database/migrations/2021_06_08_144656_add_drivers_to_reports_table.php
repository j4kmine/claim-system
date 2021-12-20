<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDriversToReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            //
            $table->string('driver_name')->after('owner_driver_relationship');
            $table->string('driver_nric')->after('driver_name');
            $table->date('driver_dob')->after('driver_nric');
            $table->date('driver_license')->after('driver_dob');
            $table->string('driver_address')->after('driver_license')->nullable();
            $table->string('driver_contact_no')->after('driver_address')->nullable();
            $table->string('driver_email')->after('driver_contact_no')->nullable();
            $table->string('driver_occupation')->after('driver_email')->nullable();
            
            $table->boolean('is_other_vehicle')->after('driver_occupation');
            $table->string('other_vehicle_car_plate')->after('is_other_vehicle')->nullable();
            $table->string('other_vehicle_make')->after('other_vehicle_car_plate')->nullable();
            $table->string('other_vehicle_model')->after('other_vehicle_make')->nullable();
            $table->string('other_driver_name')->after('other_Vehicle_model')->nullable();
            $table->string('other_driver_nric')->after('other_driver_name')->nullable();
            $table->string('other_driver_contact_no')->after('other_driver_nric')->nullable();
            $table->string('other_driver_address')->after('other_driver_contact_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            //
            $table->dropColumn('driver_name');
            $table->dropColumn('driver_nric');
            $table->dropColumn('driver_dob');
            $table->dropColumn('driver_license');
            $table->dropColumn('driver_address');
            $table->dropColumn('driver_contact_no');
            $table->dropColumn('driver_email');
            $table->dropColumn('driver_occupation');
            
            $table->dropColumn('is_other_vehicle');
            $table->dropColumn('other_vehicle_car_plate');
            $table->dropColumn('other_vehicle_make');
            $table->dropColumn('other_vehicle_model');
            $table->dropColumn('other_driver_name');
            $table->dropColumn('other_driver_nric');
            $table->dropColumn('other_driver_contact_no');
            $table->dropColumn('other_driver_address');
        });
    }
}
