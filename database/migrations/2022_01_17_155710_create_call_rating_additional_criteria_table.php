<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallRatingAdditionalCriteriaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_rating_additional_criteria', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('call_rating_id');
            $table->bigInteger('additional_criteria_id');
            $table->bigInteger('additional_criteria_option_id');
            $table->string('value');
            $table->timestamps();

            $table->foreign('call_rating_id',
                'fk_call_rating_additional_criteria_call_ratings_call_rating_id'
            )->references('id')->on('call_ratings');

            $table->foreign('additional_criteria_id',
                'fk_call_rating_additional_criteria_additional_criteria_additional_criteria_id'
            )->references('id')->on('additional_criteria');

            $table->foreign('additional_criteria_option_id',
                'fk_call_rating_additional_criteria_additional_criteria_options_additional_criteria_option_id'
            )->references('id')->on('additional_criteria_options');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('call_rating_additional_criteria');
    }
}
