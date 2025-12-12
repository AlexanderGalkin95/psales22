<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectCallSettingsIntegrationPipelinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_call_settings_integration_pipelines', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('integration_pipeline_id');
            $table->bigInteger('project_id');
            $table->timestamps();

            $table->foreign('integration_pipeline_id')
                ->references('id')
                ->on('integration_pipelines')
                ->onDelete('cascade');
            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
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
        Schema::dropIfExists('project_call_settings_integration_pipelines');
    }
}
