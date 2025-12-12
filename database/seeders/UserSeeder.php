<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@test.ru'],
            ['name' => 'admin', 'password' => bcrypt('12345678'),
        ]);
        echo "admin created: admin@test.ru/12345678\r\n";
        $roleId = DB::table('roles')->where('name', 'sa')->first()?->id;
        $userId = DB::table('users')->where('email', 'admin@test.ru')->first()?->id;
        if ($roleId && $userId) {
            DB::table('role_user')->updateOrInsert(['role_id' => $roleId, 'user_id' => $userId, 'user_type' => User::class]);
        }

    }
}
