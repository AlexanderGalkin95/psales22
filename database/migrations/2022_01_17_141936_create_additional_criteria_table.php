<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdditionalCriteriaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additional_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->longText('legend');
            $table->bigInteger('project_id');
            $table->timestamps();

            $table->foreign('project_id','fk_additional_criteria_projects_project_id')
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
        Schema::dropIfExists('additional_criteria');
    }
}
