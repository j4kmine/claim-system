<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInsurerToWarrantiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warranties', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('insurer_id')->nullable()->after('creator_id');
            $table->foreign('insurer_id')->references('id')->on('companies');
            $table->longText('remarks')->nullable()->after('start_date');
        });
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
            $table->dropColumn('remarks');
            $table->dropForeign('warranties_insurer_id_foreign');
            $table->dropColumn('insurer_id');
        });
    }
}
