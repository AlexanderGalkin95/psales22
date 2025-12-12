<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallRatingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_rating', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('audio_id')->unique();
            $table->integer('project_id');
            $table->integer('user_id');
            $table->timestamps();

            $table->foreign('user_id','fk_call_rating_users_user_id')
                ->references('id')->on('users');
            $table->foreign('project_id','fk_call_rating_projects_project_id')
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
        Schema::dropIfExists('call_rating');
    }
}
