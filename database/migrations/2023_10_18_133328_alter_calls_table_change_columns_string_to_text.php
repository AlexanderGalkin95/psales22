<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCallsTableChangeColumnsStringToText extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->text('record_event_type')->comment('Входящие|Исходящие')->change();
            $table->text('record_responsible_name')->comment('Имя ответственного')->change();
            $table->text('record_element_name')->nullable()->change();
            $table->text('record_element_link')->comment('Ссылка на Contact|Lead')->change();
            $table->text('record_status')->change();
            $table->text('record_link')->nullable()->comment('Ссылка на запись')->change();
            $table->text('record_source')->comment('Источник')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->string('record_event_type')->comment('Входящие|Исходящие')->change();
            $table->string('record_responsible_name')->comment('Имя ответственного')->change();
            $table->string('record_element_name')->nullable()->change();
            $table->string('record_element_link')->comment('Ссылка на Contact|Lead')->change();
            $table->string('record_status')->change();
            $table->string('record_link')->nullable()->comment('Ссылка на запись')->change();
            $table->string('record_source')->comment('Источник')->change();
        });
    }
}
