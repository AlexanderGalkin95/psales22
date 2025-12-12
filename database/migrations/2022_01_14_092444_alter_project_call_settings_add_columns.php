<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProjectCallSettingsAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_call_settings', function (Blueprint $table) {
            $table->renameColumn('duration_limit', 'filter_duration_from');
            $table->integer('filter_duration_to')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_call_settings', function (Blueprint $table) {
            $table->renameColumn('filter_duration_from', 'duration_limit');
            $table->dropColumn( 'filter_duration_to');
        });
    }
}
