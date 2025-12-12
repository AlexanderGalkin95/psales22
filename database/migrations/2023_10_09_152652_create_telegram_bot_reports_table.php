<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTelegramBotReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('telegram_bot_reports', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('project_id');
            $table->enum('report_status', ['running', 'error', 'queued', 'success', 'warning']);
            $table->text('error_text')->nullable();
            $table->timestamp('last_report_sent_at')->nullable();
            $table->foreign('project_id')->references('id')->on('google_projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_bot_reports');
    }
}
