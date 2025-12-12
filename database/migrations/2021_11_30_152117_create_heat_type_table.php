<?php

use App\Models\HeatType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeatTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ref_heat_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon');
            $table->string('system_name');
            $table->timestamps();
        });

        HeatType::insert([
            [ 'name' => 'Теплый', 'icon' => '♨', 'system_name' => 'warm' ],
            [ 'name' => 'Холодный', 'icon' => '🌨', 'system_name' => 'cold' ],
            [ 'name' => 'Не целевой', 'icon' => '❌', 'system_name' => 'inappropriate' ],
            [ 'name' => 'Горячий', 'icon' => '🔥', 'system_name' => 'hot' ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ref_heat_types');
    }
}
