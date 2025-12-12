<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class InsertIntoRolesNewRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Role::insert([
            [
                'name' => 'analytic',
                'display_name' => 'Аналитик',
                'created_at' => DB::raw('current_timestamp')
            ],
            [
                'name' => 'technical_support',
                'display_name' => 'Тех. поддержка',
                'created_at' => DB::raw('current_timestamp')
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Role::whereIn('name', ['analytic', 'technical_support'])->delete();
    }
}
