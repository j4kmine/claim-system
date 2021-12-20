<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemarkToClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('claims', function (Blueprint $table) {
            //
            $table->string('insurer_ref_no')->after('ref_no')->unique()->nullable(); 
            $table->longText('allcars_remarks')->after('remarks')->nullable();
        });
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
            $table->dropColumn('insurer_ref_no');
            $table->dropColumn('allcars_remarks');
        });
    }
}
