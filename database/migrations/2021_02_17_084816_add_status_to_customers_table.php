<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class AddStatusToCustomersTable extends Migration
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
            Schema::table('customers', function (Blueprint $table) {
                $table->string('status')->after('remember_token');
            });
        } else {
            /**
             * SQLITE Running these migrations
             */
            Schema::table('customers', function (Blueprint $table) {
                $table->string('status')->after('remember_token')->default('');
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
        Schema::table('customers', function (Blueprint $table) {
            //
            $table->dropColumn('status');
        });
    }
}
