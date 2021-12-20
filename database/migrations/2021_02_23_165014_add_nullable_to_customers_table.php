<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNullableToCustomersTable extends Migration
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
            $table->dropUnique(['nric']);

            $table->string('salutation')->nullable()->change();
            $table->string('name')->nullable()->change();
            $table->longText('address')->nullable()->change();
            $table->string('email')->unique()->nullable()->change();
            $table->string('phone')->unique()->nullable()->change();
            $table->string('nric_uen')->unique()->nullable()->change();
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
