<?php

use App\Models\IntegrationSchedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('type', 100);
            $table->string('status', 100)->default('created');
            $table->timestamp('runtime')->nullable();
            $table->timestamps();
        });

        /**
         * Integration Calls
         */
        Schema::table('calls', function (Blueprint $table) {
            $schedule = IntegrationSchedule::create([
                'type' => IntegrationSchedule::SCHEDULE_TYPE_CALLS,
                'status' => IntegrationSchedule::SCHEDULE_STATUS_COMPLETED
            ]);

            $table->bigInteger('schedule_id')->default($schedule->id);

            $table->foreign('schedule_id', 'fk_integration_schedules_calls_schedule_id')
                ->references('id')
                ->on('integration_schedules');
        });

        Schema::table('calls', function (Blueprint $table) {
            $table->bigInteger('schedule_id')->default(null)->change();
        });

        /**
         * Integration Sales Managers
         */
        Schema::table('sales_managers', function (Blueprint $table) {
            $schedule = IntegrationSchedule::create([
                'type' => IntegrationSchedule::SCHEDULE_TYPE_MANAGERS,
                'status' => IntegrationSchedule::SCHEDULE_STATUS_COMPLETED
            ]);

            $table->bigInteger('schedule_id')->default($schedule->id);

            $table->foreign('schedule_id', 'fk_integration_schedules_sales_managers_schedule_id')
                ->references('id')
                ->on('integration_schedules');
        });

        Schema::table('sales_managers', function (Blueprint $table) {
            $table->bigInteger('schedule_id')->default(null)->change();
        });

        /**
         * Integration Pipelines
         */
        Schema::table('integration_pipelines', function (Blueprint $table) {
            $schedule = IntegrationSchedule::create([
                'type' => IntegrationSchedule::SCHEDULE_TYPE_PIPELINES,
                'status' => IntegrationSchedule::SCHEDULE_STATUS_COMPLETED
            ]);

            $table->bigInteger('schedule_id')->default($schedule->id);

            $table->foreign('schedule_id', 'fk_integration_schedules_integration_pipelines_schedule_id')
                ->references('id')
                ->on('integration_schedules');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('calls', ['schedule_id']);
        Schema::dropColumns('sales_managers', ['schedule_id']);
        Schema::dropColumns('integration_pipelines', ['schedule_id']);

        Schema::dropIfExists('integration_schedules');
    }
}
