<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectManagerAssessorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_manager_assessors', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('project_id');
            $table->bigInteger('user_id');
            $table->bigInteger('project_call_settings_sales_manager_id');
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_call_settings_sales_manager_id')->references('id')->on('project_call_settings_sales_managers')->onDelete('cascade');

            $table->unique(['project_id', 'user_id', 'project_call_settings_sales_manager_id']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('tasks_generation_status')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_manager_assessors');

        Schema::dropColumns('projects', ['tasks_generation_status']);
    }
}
