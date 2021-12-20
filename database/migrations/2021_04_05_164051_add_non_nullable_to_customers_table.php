<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNonNullableToCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            //
            $table->dropUnique(['email']);
            $table->dropUnique(['phone']);
            $table->dropUnique(['nric_uen']);

            $table->string('salutation')->change();
            $table->string('name')->change();
            $table->longText('address')->change();
            $table->string('email')->unique()->change();
            $table->string('phone')->unique()->change();
            $table->string('nric_uen')->unique()->change();
        });
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
        });
    }
}
