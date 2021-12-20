<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeToCustomers extends Migration
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
            $table->string('stripe_id')->nullable()->collation('utf8mb4_bin')->after('driving_license_validity');
            $table->string('card_brand')->nullable()->after('stripe_id');
            $table->string('card_last_four', 4)->nullable()->after('card_brand');
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
            $table->dropColumn('stripe_id');
            $table->dropColumn('card_brand');
            $table->dropColumn('card_last_four');
        });
    }
}
