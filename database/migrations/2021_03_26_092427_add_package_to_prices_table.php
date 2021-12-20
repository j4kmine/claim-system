<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class AddPackageToPricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warranty_prices', function (Blueprint $table) {
            /**
             * SQL Error
             * SQLSTATE[HY000]: General error: 1 Cannot add a NOT NULL column with default value NULL
             */
            if (!App::runningUnitTests()) {
                $table->string('package')->after('insurer_id');
            }else{
                $table->string('package')->after('insurer_id')->default('');
            }
        });
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
            $table->dropColumn('package');
        });
    }
}
