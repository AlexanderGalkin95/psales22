<?php

use App\Models\Integration;
use App\Models\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProjectAddColumns1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->integer('integration_id')->nullable();
            $table->integer('project_type')->nullable();
            $table->renameColumn('amocrm_name', 'integration_domain');
            $table->renameColumn('amo_crm_status', 'integration_status');

            $table->foreign('integration_id','fk_integrations_projects_integration_id')
                ->references('id')->on('integrations');
            $table->foreign('project_type','fk_ref_integrations_projects_project_type')
                ->references('id')->on('ref_integrations')
                ->onDelete('SET NULL');
        });

        $types = Integration::all();
        Project::whereNotNull('amo_code_id')->each(function ($project) use ($types) {
            $integration = $types->firstWhere('integration_id', $project->amo_code_id);
            $project->update([
                'integration_id' => $integration ? $integration->id : null,
                'project_type' => $integration ? $integration->ref_integration_id : null,
            ]);
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
            $table->renameColumn('integration_domain', 'amocrm_name');
            $table->renameColumn('integration_status', 'amo_crm_status');
        });
        Schema::dropColumns('projects', ['integration_id', 'project_type']);
    }
}
