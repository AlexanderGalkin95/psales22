<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectCallSettingsSalesManagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_call_settings_sales_managers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('project_id');
            $table->bigInteger('sales_manager_id');
            $table->integer('duration_limit');
            $table->boolean('no_duration_limit')->default(false);
            $table->timestamps();

            $table->unique(['project_id', 'sales_manager_id']);
            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('sales_manager_id')->references('id')->on('sales_managers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_call_settings_sales_managers');
    }
}
