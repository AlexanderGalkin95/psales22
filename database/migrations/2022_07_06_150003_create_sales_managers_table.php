<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesManagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_managers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('foreign_manager_id')->unique();
            $table->bigInteger('project_id');
            $table->bigInteger('intergation_id');
            $table->string('name');
            $table->string('email');
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->bigInteger('group_id')->nullable();
            $table->string('group_name')->nullable();
            $table->string('source')->nullable();
            $table->timestamps();

            $table->foreign('project_id', 'fk_sales_managers_projects_project_id')
                ->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_managers');
    }
}
