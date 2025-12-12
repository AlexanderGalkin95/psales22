<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('roles')
            ->insert([
                [
                    'name' => 'sa',
                    'display_name' => 'Администратор',
                    'created_at' => DB::raw('current_timestamp')
                ],                [
                    'name' => 'pm',
                    'display_name' => 'Проектный менеджер',
                    'created_at' => DB::raw('current_timestamp')
                ],                [
                    'name' => 'senior_assessor',
                    'display_name' => 'Старший ассессор',
                    'created_at' => DB::raw('current_timestamp')
                ],                [
                    'name' => 'assessor',
                    'display_name' => 'Ассессор',
                    'created_at' => DB::raw('current_timestamp')
                ]
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('roles')->delete();
    }
}
