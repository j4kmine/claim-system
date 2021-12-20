<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class AddSelectedToWarrantiesTable extends Migration
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
         * PDOException: SQLSTATE[HY000]: General error: 1 near "NOT": syntax error
         * Probable causes:
         * - SQLite doesnt support change() function
         */
        if (!App::runningUnitTests()) {
            Schema::table('warranties', function (Blueprint $table) {
                $table->date('start_date')->nullable()->change();
                $table->unsignedDecimal('price', 10, 2)->nullable()->change();
                $table->string('ci_no')->after('ref_no')->nullable()->unique();
            });

            Schema::table('warranty_prices', function (Blueprint $table) {
                $table->unsignedDecimal('price', 10, 2)->change();
            });
        } else {
            /**
             * SQLITE Running these migrations
             */
            Schema::table('warranties', function (Blueprint $table) {
                $table->dropColumn('start_date');
            });
            Schema::table('warranties', function (Blueprint $table) {
                $table->dropColumn('price');
            });
            Schema::table('warranties', function (Blueprint $table) {

                $table->date('start_date')->nullable()->default('');
                $table->unsignedDecimal('price', 10, 2)->nullable()->default(0);
                $table->string('ci_no')->after('ref_no')->nullable()->unique()->default('');
            });

            Schema::table('warranty_prices', function (Blueprint $table) {
                $table->dropColumn('price');
            });

            Schema::table('warranty_prices', function (Blueprint $table) {
                $table->unsignedDecimal('price', 10, 2)->default(0);
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
        });
    }
}
