<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectCallSettingsIntegrationPipelineStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_call_settings_integration_pipeline_statuses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('project_call_settings_integration_pipeline_id');
            $table->bigInteger('integration_pipeline_status_id');
            $table->timestamps();

            $table->foreign('project_call_settings_integration_pipeline_id')
                ->references('id')
                ->on('project_call_settings_integration_pipelines')
                ->onDelete('cascade');
            $table->foreign('integration_pipeline_status_id')
                ->references('id')
                ->on('integration_pipeline_statuses')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_call_settings_integration_pipeline_statuses');
    }
}
