<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColToStripeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stripe', function (Blueprint $table) {
            //
            $table->dropColumn('client_secret');
            $table->dropColumn('receipt_url');
            $table->dropColumn('receipt_number');
            $table->dropColumn('deleted_at');
            $table->string('payment_id')->unique()->after('customer_id');
        });
    }
 
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stripe', function (Blueprint $table) {
            //
            $table->dropColumn('payment_id');
        });
    }
}
