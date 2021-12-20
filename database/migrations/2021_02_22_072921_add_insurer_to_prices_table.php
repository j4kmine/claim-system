<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class AddInsurerToPricesTable extends Migration
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
            Schema::table('warranty_prices', function (Blueprint $table) {
                $table->unsignedBigInteger('insurer_id')->after('warranty_duration');
                $table->foreign('insurer_id')->references('id')->on('companies');
            });
        } else {
            /**
             * SQLITE Running these migrations
             */
            Schema::table('warranty_prices', function (Blueprint $table) {
                $table->unsignedBigInteger('insurer_id')->after('warranty_duration')->default(0);
                $table->foreign('insurer_id')->references('id')->on('companies');
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
        Schema::table('warranty_prices', function (Blueprint $table) {
            //
            $table->dropForeign('warranty_prices_insurer_id_foreign');
            $table->dropColumn('insurer_id');
        });
    }
}
