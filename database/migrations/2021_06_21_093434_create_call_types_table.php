<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCallTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name');
            $table->string('system_name');
            $table->timestamps();
        });
        DB::table('call_types')->insert([
            ['name' => 'Реаним', 'short_name' => 'Реаним', 'system_name' => 'reanimation'],
            ['name' => 'Квалиф', 'short_name' => 'Квалиф', 'system_name' => 'qualification'],
            ['name' => 'после КП', 'short_name' => 'Квалиф', 'system_name' => 'after_com_prop'],
            ['name' => 'Возраж', 'short_name' => 'Возраж', 'system_name' => 'objection'],
            ['name' => 'КЭВ', 'short_name' => 'КЭВ', 'system_name' => 'efficiency_rate'],
            ['name' => 'Ускор', 'short_name' => 'Ускор', 'system_name' => 'accelerated'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('call_types');
    }
}
