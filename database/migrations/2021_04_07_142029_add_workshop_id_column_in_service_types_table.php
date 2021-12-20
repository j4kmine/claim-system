<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWorkshopIdColumnInServiceTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_types', function (Blueprint $table) {
            if (!App::runningUnitTests()) {
                $table->foreignId('workshop_id')
                    ->nullable()
                    ->after('id')
                    ->references('id')
                    ->on('companies');
            } else {
                $table->foreignId('workshop_id')->default(1)
                    ->references('id')
                    ->on('companies');
            }

            $table->string('status')->default('active')->after('name');
            $table->string('description')->after('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropForeign('service_types_workshop_id_foreign');
            $table->dropColumn('workshop_id');
            $table->dropColumn('status');
            $table->dropColumn('description');
        });
    }
}
