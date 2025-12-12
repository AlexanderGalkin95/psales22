<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTelegramBotReportQueuesTableAddOndeleteCascade extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('telegram_bot_report_queues', function (Blueprint $table) {
            $table->dropForeign(['report_id']);
            $table->foreign('report_id')->references('id')->on('telegram_bot_reports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('telegram_bot_report_queues', function (Blueprint $table) {
            $table->dropForeign(['report_id']);
            $table->foreign('report_id')->references('id')->on('telegram_bot_reports');
        });
    }
}
