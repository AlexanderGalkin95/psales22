<?php

use App\Models\AmoCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Call;
use App\Models\SalesManager;

class AlterTablesDropColumnProjectId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_managers', function (Blueprint $table) {
            $table->bigInteger('project_id')->nullable()->change();
            $table->renameColumn('intergation_id', 'integration_id');

            $table->dropForeign('fk_sales_managers_projects_project_id');
        });

        Schema::table('calls', function (Blueprint $table) {
            $table->bigInteger('project_id')->nullable()->change();

            $table->bigInteger('integration_id')->nullable();

            $table->foreign('integration_id')->references('id')->on('integrations');
        });

        Schema::table('integration_pipelines', function (Blueprint $table) {
            $table->bigInteger('project_id')->nullable()->change();
        });

        $this->reassignValuesInCalls();

        Schema::table('sales_managers', function (Blueprint $table) {
            $table->foreign('integration_id', 'fk_sales_managers_integrations_integration_id')
                ->references('id')
                ->on('integrations');
        });

        Schema::table('calls', function (Blueprint $table) {
            $table->bigInteger('integration_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_managers', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_managers', 'project_id')) {
                $table->bigInteger('project_id')->nullable(false);
            } else {
                $table->bigInteger('project_id')->nullable(false)->change();
            }
            $table->renameColumn('integration_id', 'intergation_id');

            $table->dropForeign('fk_sales_managers_integrations_integration_id');
            $table->foreign('project_id', 'fk_sales_managers_projects_project_id')
                ->references('id')->on('projects');
        });

        Schema::dropColumns('calls', ['integration_id']);
    }

    private function reassignValuesInCalls()
    {
        SalesManager::all()->each(function ($manager) {
            $amoCode = AmoCode::find($manager->integration_id);
            if ($amoCode) {
                $manager->integration_id = $amoCode->integration->id;
                $manager->save();
            }
        });

        Call::with('project')
            ->each(function ($call) {
                if (!$call->project) {
                    $call->delete();
                } else {
                    $call->integration_id = $call->project->integration_id;
                    $call->save();
                }
            });
    }
}
