<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateExtensionFingerprintsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto";');
        Schema::create('extension_fingerprints', function (Blueprint $table) {
            $table->integerIncrements('id');
            $table->uuid('uuid')->default(DB::raw('gen_random_uuid()'));
            $table->string('extension_id');
            $table->integer('user_id')->nullable(); // This stays null until a user is logged in
            $table->string('fingerprint');
            $table->string('languages');
            $table->string('screen_resolution');
            $table->string('platform');
            $table->string('vendor');
            $table->string('timezone');
            $table->string('device')->nullable();
            $table->string('is_active')->default(true);
            $table->timestamps();

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
        Schema::dropIfExists('extension_fingerprints');
        DB::statement('DROP EXTENSION IF EXISTS "pgcrypto";');
    }
}
