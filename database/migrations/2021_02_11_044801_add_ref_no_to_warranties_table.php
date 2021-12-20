<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class AddRefNoToWarrantiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * SQLite error
         * Doctrine\DBAL\Driver\PDO\Exception: SQLSTATE[HY000]: General error: 1 Cannot add a NOT NULL column with default value NULL
         */
        if (!App::runningUnitTests()) {
            Schema::table('warranties', function (Blueprint $table) {
                $table->string('ref_no')->unique()->after('id');
                $table->unsignedBigInteger('dealer_id')->after('vehicle_id');
                $table->foreign('dealer_id')->references('id')->on('companies');
                $table->unsignedBigInteger('creator_id')->after('dealer_id');
                $table->foreign('creator_id')->references('id')->on('users');
                $table->string('status')->after('start_date');
            });
        } else {
            /**
             * SQLITE Running these migrations
             */
            Schema::table('warranties', function (Blueprint $table) {
                $table->string('ref_no')->unique()->after('id')->default('');
                $table->unsignedBigInteger('dealer_id')->after('vehicle_id')->default(0);
                $table->foreign('dealer_id')->references('id')->on('companies');
                $table->unsignedBigInteger('creator_id')->after('dealer_id')->default(0);
                $table->foreign('creator_id')->references('id')->on('users');
                $table->string('status')->after('start_date')->default('');
            });
        }
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
            $table->dropForeign(['dealer_id']);
            $table->dropForeign(['creator_id']);
            $table->dropColumn('ref_no');
            $table->dropColumn('dealer_id');
            $table->dropColumn('creator_id');
            $table->dropColumn('status');
        });
    }
}
