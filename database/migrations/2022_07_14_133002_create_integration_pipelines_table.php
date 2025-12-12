<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationPipelinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_pipelines', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('integration_id');
            $table->bigInteger('project_id');
            $table->bigInteger('pipeline_id')->unique();
            $table->string('name');
            $table->bigInteger('sort');
            $table->boolean('is_main');
            $table->boolean('is_unsorted_on');
            $table->boolean('is_archive');
            $table->bigInteger('account_id');
            $table->string('source');
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('integration_id')->references('id')->on('integrations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('integration_pipelines');
    }
}
