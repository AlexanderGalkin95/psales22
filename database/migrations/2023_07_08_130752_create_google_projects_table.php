<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoogleProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('telegram')->unique();
            $table->string('whatsapp')->unique()->nullable();
            $table->time('report_time')->default('17:00');
            $table->json('managers')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('google_projects');
    }
}
