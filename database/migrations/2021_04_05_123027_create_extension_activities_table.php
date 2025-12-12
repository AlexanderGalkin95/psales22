<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtensionActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extension_activities', function (Blueprint $table) {
            $table->id();
            $table->integer('extension_id');
            $table->integer('user_id');
            $table->boolean('enabled')->default(false);
            $table->boolean('online')->default(false);
            $table->timestamp('online_date')->nullable();
            $table->timestamp('offline_date')->nullable();

            $table->foreign(
                'extension_id',
                'fk_extension_activities_extension_fingerprints_extension_id')
                ->references('id')
                ->on('extension_fingerprints');

            $table->foreign(
                'user_id',
                'fk_extension_fingerprints_users_user_id')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extension_activities');
    }
}
