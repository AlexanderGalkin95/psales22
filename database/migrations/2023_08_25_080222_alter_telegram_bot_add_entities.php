<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTelegramBotAddEntities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('telegram_bot', function (Blueprint $table) {
            $table->dropForeign('fk_telegram_bot_daily_reports_project_id');
            $table->foreign('project_id', 'fk_telegram_bot_daily_reports_google_project_id')
                ->references('id')
                ->on('google_projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
