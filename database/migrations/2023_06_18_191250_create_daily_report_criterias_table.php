<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyReportCriteriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_report_criterias', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('daily_report_id');
            $table->bigInteger('criteria_id')->nullable();
            $table->string('criteria');
            $table->integer('value')->nullable();
            $table->timestamps();

            $table->foreign('daily_report_id', 'fk_daily_report_criterias_daily_reports_daily_report_id')
                ->references('id')
                ->on('daily_reports');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_report_criterias');
    }
}
