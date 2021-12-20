<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToMotorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('motors', function (Blueprint $table) {
            //
            $table->string('usage')->nullable()->after('ref_no');
            $table->integer('point')->nullable()->after('usage');
            $table->string('ci_no')->unique()->nullable()->after('ref_no');
            $table->string('policy_no')->unique()->nullable()->after('ci_no');
            $table->unsignedDecimal('price', 10, 2)->nullable()->after('usage');
            $table->boolean('policyholder_driving')->nullable()->after('point');
            $table->string('start_date')->nullable()->after('policyholder_driving');
            $table->string('expiry_date')->nullable()->after('start_date');
            $table->string('remarks')->nullable()->after('expiry_date');
            $table->unsignedBigInteger('proposer_id')->nullable()->after('remarks');
            $table->foreign('proposer_id')->references('id')->on('proposers');
            $table->unsignedBigInteger('driver_id')->nullable()->after('remarks');
            $table->foreign('driver_id')->references('id')->on('drivers');
            $table->unsignedBigInteger('vehicle_id')->nullable()->after('remarks');
            $table->foreign('vehicle_id')->references('id')->on('vehicles');
            $table->unsignedBigInteger('creator_id')->nullable()->after('remarks');
            $table->foreign('creator_id')->references('id')->on('users');
            $table->unsignedBigInteger('dealer_id')->nullable()->after('remarks');
            $table->foreign('dealer_id')->references('id')->on('companies');
            $table->unsignedBigInteger('insurer_id')->nullable()->after('remarks');
            $table->foreign('insurer_id')->references('id')->on('companies');
            $table->string('status')->nullable()->after('proposer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('motors', function (Blueprint $table) {
            //
            $table->dropColumn('usage');
            $table->dropColumn('point');
            $table->dropColumn('price');
            $table->dropColumn('policyholder_driving');
            $table->dropColumn('start_date');
            $table->dropColumn('expiry_date');
            $table->dropColumn('remarks');

            $table->dropForeign(['proposer_id']);
            $table->dropForeign(['driver_id']);
            $table->dropForeign(['vehicle_id']);
            $table->dropForeign(['creator_id']);
            $table->dropForeign(['dealer_id']);
            $table->dropColumn('proposer_id');
            $table->dropColumn('driver_id');
            $table->dropColumn('vehicle_id');
            $table->dropColumn('creator_id');
            $table->dropColumn('dealer_id');
            $table->dropColumn('status');
        });
    }
}
