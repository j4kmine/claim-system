<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProposerToWarrantiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warranties', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('customer_id')->nullable()->change();
            $table->unsignedBigInteger('proposer_id')->after('vehicle_id')->nullable();
            $table->foreign('proposer_id')->references('id')->on('proposers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warranties', function (Blueprint $table) {
            //
            $table->dropForeign('warranties_proposer_id_foreign');
            $table->dropColumn('proposer_id'); 
        });
    }
}
