<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColsToDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('drivers', function (Blueprint $table) {
            //
            $table->string('name')->nullable()->change();
            $table->string('nric_type')->nullable()->after('name');
            $table->string('nationality')->nullable()->after('nric'); 
            $table->integer('residential')->nullable()->after('nationality'); 
            $table->string('gender')->nullable()->after('name'); 
            $table->integer('no_of_accidents')->nullable()->after('residential');
            $table->integer('total_claim')->nullable()->after('no_of_accidents');
            $table->integer('ncd')->nullable()->after('total_claim');
            $table->boolean('serious_offence')->nullable()->after('ncd');
            $table->boolean('physical_disable')->nullable()->after('serious_offence');
            $table->boolean('refused')->nullable()->after('physical_disable');
            $table->boolean('terminated')->nullable()->after('refused');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('drivers', function (Blueprint $table) {
            //
            $table->dropColumn('nric_type');
            $table->dropColumn('nationality'); 
            $table->dropColumn('residential'); 
            $table->dropColumn('gender'); 
            $table->dropColumn('no_of_accidents');
            $table->dropColumn('total_claim');
            $table->dropColumn('ncd');
            $table->dropColumn('serious_offence');
            $table->dropColumn('physical_disable');
            $table->dropColumn('refused');
            $table->dropColumn('terminated');
        });
    }
}
