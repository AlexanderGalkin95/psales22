<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('project_id');
            $table->integer('criteria_id');
            $table->integer('call_type_id');
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('criteria_id')->references('id')->on('criteria');
            $table->foreign('call_type_id')->references('id')->on('call_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_settings');
    }
}
