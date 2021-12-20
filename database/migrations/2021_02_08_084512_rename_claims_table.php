<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class RenameClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * SQLite Error
         * BadMethodCallException: SQLite doesn't support multiple calls to dropColumn / renameColumn in a single modification.
         */
        if (!App::runningUnitTests()) {
            Schema::table('claims', function (Blueprint $table) {
                $table->renameColumn('verified_at', 'approved_at');
                $table->renameColumn('completed_at', 'repaired_at');
            });
        } else {
            /**
             * SQLITE Running these migrations
             */
            Schema::table('claims', function (Blueprint $table) {
                $table->renameColumn('verified_at', 'approved_at');
            });
            Schema::table('claims', function (Blueprint $table) {
                $table->renameColumn('completed_at', 'repaired_at');
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
        Schema::table('claims', function (Blueprint $table) {
            //
        });
    }
}
