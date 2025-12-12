<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableTelegramBotDropColumnProjectId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('telegram_bot', function (Blueprint $table) {
            $table->dropForeign('fk_telegram_bot_daily_reports_google_project_id');
            $table->dropColumn('project_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('telegram_bot', function (Blueprint $table) {
            $table->bigInteger('project_id')->nullable();
            $table->foreign('project_id', 'fk_telegram_bot_daily_reports_google_project_id')
                ->references('id')
                ->on('google_projects');
        });
    }
}
