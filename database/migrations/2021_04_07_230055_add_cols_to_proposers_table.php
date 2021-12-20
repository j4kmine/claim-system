<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToProposersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('proposers', function (Blueprint $table) {
            //
            $table->integer('nric_type')->nullable()->after('id');
            $table->string('nationality')->nullable()->after('name');
            $table->string('residential')->nullable()->after('nationality');
            $table->string('gender')->nullable()->after('name');
            $table->string('occupation')->nullable()->after('gender'); 
            $table->string('postal_code')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('proposers', function (Blueprint $table) {
            //
            $table->dropColumn('nric_type');
            $table->dropColumn('nationality');
            $table->dropColumn('residential');
            $table->dropColumn('gender');
            $table->dropColumn('occupation');
            $table->dropColumn('postal_code');
        });
    }
}
