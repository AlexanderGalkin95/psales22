<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableProjectsRenameColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('pm', 'pm_id');
            $table->renameColumn('senior', 'senior_id');
            $table->renameColumn('assessor', 'assessor_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('pm_id', 'pm');
            $table->renameColumn('senior_id', 'senior');
            $table->renameColumn('assessor_id', 'assessor');
        });
    }
}
