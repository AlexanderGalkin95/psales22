<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateExtensionStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('extension_states', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('system_name');
        });
        DB::table('extension_states')->insert([
            ['name' => 'Активно', 'system_name' => 'active'],
            ['name' => 'Заблокировано', 'system_name' => 'blocked'],
            ['name' => 'Не установлено', 'system_name' => 'absent'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extension_states');
    }
}
