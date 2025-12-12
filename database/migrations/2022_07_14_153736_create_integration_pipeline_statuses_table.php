<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationPipelineStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_pipeline_statuses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('integration_pipeline_id');
            $table->bigInteger('pipeline_id');
            $table->bigInteger('status_id');
            $table->string('name');
            $table->bigInteger('sort');
            $table->boolean('is_editable');
            $table->string('color');
            $table->integer('type');
            $table->bigInteger('account_id');
            $table->timestamps();

            $table->unique(['pipeline_id', 'status_id']);

            $table->foreign('integration_pipeline_id')
                ->references('id')
                ->on('integration_pipelines')
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
        Schema::dropIfExists('integration_pipeline_statuses');
    }
}
