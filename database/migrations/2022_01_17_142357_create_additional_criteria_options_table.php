<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdditionalCriteriaOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additional_criteria_options', function (Blueprint $table) {
            $table->id();
            $table->string('label', 50);
            $table->string('value', 50);
            $table->bigInteger('additional_criteria_id');
            $table->timestamps();

            $table->foreign('additional_criteria_id','fk_additional_criteria_options_additional_criteria_additional_criteria_id')
                ->references('id')->on('additional_criteria');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('additional_criteria_options');
    }
}
