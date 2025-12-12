<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

class UpdateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Role::where(['name' => 'assessor'])->update(['display_name' => 'Асессор']);
        Role::where(['name' => 'senior_assessor'])->update(['display_name' => 'Старший асессор']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
