<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class DropProjectIdForeignkeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ($this->hasColumn('calls', 'project_id')) {
            Schema::dropColumns('calls', ['project_id']);
        }

        if ($this->hasColumn('integration_pipelines', 'project_id')) {
            Schema::dropColumns('integration_pipelines', ['project_id']);
        }

        if ($this->hasColumn('projects', 'amo_code_id')) {
            Schema::dropColumns('projects', ['amo_code_id']);
        }

        if ($this->hasColumn('sales_managers', 'project_id')) {
            Schema::dropColumns('sales_managers', ['project_id']);
        }

        Schema::table('call_ratings', function (Blueprint $table) {
            if (!$this->hasForeignKey('call_ratings', 'call_ratings_call_type_id_foreign')) {
                $table->foreign('call_type_id')->references('id')->on('project_call_types');
            }
        });

        Schema::table('criteria', function (Blueprint $table) {
            if (!$this->hasForeignKey('criteria', 'criteria_project_id_foreign')) {
                $table->foreign('project_id')->references('id')->on('projects');
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            if (!$this->hasForeignKey('projects', 'projects_senior_id_foreign')) {
                $table->foreign('senior_id')->references('id')->on('users');
            }
            if (!$this->hasForeignKey('projects', 'projects_assessor_id_foreign')) {
                $table->foreign('assessor_id')->references('id')->on('users');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

    private function hasColumn($table, $column)
    {
        return Schema::hasColumn($table, $column);
    }

    private function hasForeignKey($table, $indexName)
    {
        return Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableDetails($table)
            ->hasForeignKey($indexName);
    }
}
