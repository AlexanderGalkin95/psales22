<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallRatingCrmFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_rating_crm_fields', function (Blueprint $table) {
            $table->id();
            $table->integer('call_rating_id');
            $table->integer('crm_field_id')->nullable();
            $table->string('value')->nullable();
            $table->timestamps();

            $table->foreign('call_rating_id','fk_call_rating_crm_fields_call_ratings_call_rating_id')
                ->references('id')->on('call_ratings');
            $table->foreign('crm_field_id','fk_call_rating_crm_fields_project_crm_fields_crm_field_id')
                ->references('id')->on('project_crm_fields');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('call_rating_crm_fields');
    }
}
