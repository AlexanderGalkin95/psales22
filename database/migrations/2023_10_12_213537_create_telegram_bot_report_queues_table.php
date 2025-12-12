<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTelegramBotReportQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('telegram_bot_report_queues', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('report_id');
            $table->enum('report_status', ['running', 'error', 'queued', 'success']);
            $table->text('error_text')->nullable();
            $table->timestamps();
            $table->foreign('report_id')->references('id')->on('telegram_bot_reports');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_bot_report_queues');
    }
}
