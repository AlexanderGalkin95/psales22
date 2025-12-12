<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->integer('project_id')->index();
            $table->bigInteger('record_id')->unique()->comment('Идентификатор примечания в AmoCRM');
            $table->timestamp('record_created_at')->comment('Дата создания записи в AmoCRM');
            $table->string('record_event_type')->comment('Входящие|Исходящие');
            $table->bigInteger('record_responsible_id')->comment('Ответственный');
            $table->string('record_responsible_name')->comment('Имя ответственного');
            $table->bigInteger('record_element_id')->comment('Contact|Lead ID');
            $table->string('record_element_name')->nullable();
            $table->integer('record_element_type')->comment('1:Contacts,2:Leads');
            $table->string('record_element_link')->comment('Ссылка на Contact|Lead');
            $table->string('record_status');
            $table->time('record_duration')->nullable()->comment('Длина записи');
            $table->integer('record_file_id')->nullable()->comment('идентификатор файла записи, если существует');
            $table->string('record_link')->nullable()->comment('Ссылка на запись');
            $table->string('record_source')->comment('Источник');
            $table->timestamps();

            $table->foreign('project_id','fk_calls_projects_project_id')
                ->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('calls');
    }
}
