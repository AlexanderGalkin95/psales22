<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCallRatingsChangeUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('call_ratings', function (Blueprint $table) {
            $table->dropUnique('call_rating_audio_id_unique');
            $table->unique(['audio_id', 'project_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('call_ratings', function (Blueprint $table) {
            $table->dropUnique(['audio_id', 'project_id']);
            $table->unique('audio_id','call_rating_audio_id_unique');
        });
    }
}
