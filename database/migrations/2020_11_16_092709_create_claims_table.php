<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->foreign('vehicle_id')->references('id')->on('vehicles');
            $table->unsignedBigInteger('creator_id');
            $table->foreign('creator_id')->references('id')->on('users');
            $table->unsignedBigInteger('workshop_id');
            $table->foreign('workshop_id')->references('id')->on('companies');
            $table->unsignedBigInteger('insurer_id')->nullable();
            $table->foreign('insurer_id')->references('id')->on('companies');
            $table->string('ref_no')->unique();
            $table->string('policy_name')->nullable();
            $table->string('policy_certificate_no')->nullable();
            $table->date('policy_coverage_from')->nullable();
            $table->date('policy_coverage_to')->nullable();
            $table->string('policy_nric_uen')->nullable();
            $table->date('date_of_notification')->nullable();
            $table->date('date_of_loss')->nullable();
            $table->string('cause_of_damage')->nullable();
            $table->decimal('total_claim_amount', 10, 2)->nullable();
            $table->string('remarks')->nullable();
            $table->boolean('insurer_to_allcars_payment')->default(0);
            $table->boolean('allcars_to_workshop_payment')->default(0);
            $table->boolean('above_2k')->default(0);
            $table->integer('surveyor_review_count')->default(0);
            $table->string('status');
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('claims');
    }
}
