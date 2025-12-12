<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallRatingCriteriaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_rating_criteria', function (Blueprint $table) {
            $table->id();
            $table->integer('call_rating_id');
            $table->integer('criteria_id')->nullable();
            $table->string('value')->nullable();
            $table->timestamps();

            $table->foreign('call_rating_id','fk_call_rating_criteria_call_ratings_call_rating_id')
                ->references('id')->on('call_ratings');
            $table->foreign('criteria_id','fk_call_rating_criteria_criteria_criteria_id')
                ->references('id')->on('criteria');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('call_rating_criteria');
    }
}
