<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDailyReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('project_id');
            $table->bigInteger('user_id')->nullable();
            $table->string('manager');
            $table->bigInteger('fg')->nullable();
            $table->bigInteger('crm')->nullable();
            $table->date('created_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->date('updated_at')->nullable()->onUpdate(DB::raw('CURRENT_TIMESTAMP'));

            $table->unique(['project_id', 'manager', 'created_at']);
            $table->foreign('project_id', 'fk_daily_reports_projects_project_id')
                ->references('id')
                ->on('projects');
            $table->foreign('user_id', 'fk_daily_reports_users_user_id')
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
        Schema::dropIfExists('daily_reports');
    }
}
