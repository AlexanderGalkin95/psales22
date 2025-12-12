<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallRatingObjectionFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_rating_objection_fields', function (Blueprint $table) {
            $table->id();
            $table->integer('call_rating_id');
            $table->integer('objection_field_id')->nullable();
            $table->string('value')->nullable();
            $table->string('google_column');
            $table->integer('objection_rate')->nullable();
            $table->string('google_column_rate');
            $table->timestamps();

            $table->foreign('call_rating_id','fk_call_rating_objection_fields_call_ratings_call_rating_id')
                ->references('id')->on('call_ratings');
            $table->foreign('objection_field_id','fk_call_rating_objection_fields_project_crm_fields_objection_field_id')
                ->references('id')->on('project_objection_fields');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('call_rating_objection_fields');
    }
}
