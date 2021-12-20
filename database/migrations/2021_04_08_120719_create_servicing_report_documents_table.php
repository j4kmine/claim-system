<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicingReportDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicing_report_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('servicing_report_id')->references('id')->on('servicing_reports');
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->string('url');
            $table->softDeletes();
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
        Schema::dropIfExists('servicing_report_documents');
    }
}
