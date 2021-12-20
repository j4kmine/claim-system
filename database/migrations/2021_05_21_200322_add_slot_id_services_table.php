<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlotIdServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('servicing_slot_id')->after('workshop_id')->nullable();
            $table->foreign('servicing_slot_id')->references('id')->on('servicing_slots');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            //
            $table->dropForeign('servicing_slot_id');
            $table->dropColumn('servicing_slot_id');
        });
    }
}
