<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterGoogleProjectsTableAddColumnSendingPeriod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('google_projects', function (Blueprint $table) {
            $table->json('sending_period')->nullable();
            $table->boolean('sending_include_holidays')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropColumns('google_projects', ['sending_period', 'sending_include_holidays']);
    }
}
