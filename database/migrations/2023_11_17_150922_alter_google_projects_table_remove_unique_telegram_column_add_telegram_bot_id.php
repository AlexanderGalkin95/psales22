<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterGoogleProjectsTableRemoveUniqueTelegramColumnAddTelegramBotId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('google_projects', function (Blueprint $table) {
            $table->dropUnique(['telegram']);
            $table->bigInteger('telegram_bot_id')->nullable();
            $table->foreign('telegram_bot_id')->references('id')->on('telegram_bot');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('google_projects', function (Blueprint $table) {
            $table->dropForeign(['telegram_bot_id']);
            $table->dropColumn('telegram_bot_id');
            $table->unique('telegram');
        });
    }
}
