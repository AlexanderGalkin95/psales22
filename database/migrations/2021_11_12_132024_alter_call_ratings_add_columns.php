<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCallRatingsAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('call_ratings', function (Blueprint $table) {
            $table->string('type')->nullable();
            $table->longText('comments')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->time('created_time')->nullable();
            $table->time('duration')->nullable();
            $table->string('audio_link', 500)->nullable();
            $table->string('link_to_lead')->nullable();
            $table->string('manager')->nullable();
            $table->integer('call_type_id')->nullable();
            $table->string('call_type_value')->nullable();
            $table->string('heat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('call_ratings', [
            'comments',
            'call_type_id',
            'call_type_value',
            'heat',
            'type',
            'created_date',
            'created_time',
            'duration',
            'audio_link',
            'link_to_lead',
            'manager',
        ]);
    }
}
