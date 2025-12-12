<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->updateOrInsert(['name' => 'sa'], ['display_name' => 'Администратор', 'description' => null]);
        DB::table('roles')->updateOrInsert(['name' => 'pm'], ['display_name' => 'Проектный менеджер', 'description' => null]);
        DB::table('roles')->updateOrInsert(['name' => 'senior_assessor'], ['display_name' => 'Старший асессор', 'description' => null]);
        DB::table('roles')->updateOrInsert(['name' => 'assessor'], ['display_name' => 'Асессор', 'description' => null]);
        DB::table('roles')->updateOrInsert(['name' => 'analytic'], ['display_name' => 'Аналитик', 'description' => null]);
        DB::table('roles')->updateOrInsert(['name' => 'technical_support'], ['display_name' => 'Тех. поддержка', 'description' => null]);
    }
}
