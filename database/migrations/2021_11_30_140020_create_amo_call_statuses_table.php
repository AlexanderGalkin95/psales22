<?php

use App\Models\AmoCallStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoCallStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ref_amo_call_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('system_name');
            $table->timestamps();
        });

        AmoCallStatus::insert([
            [ 'name' => 'Оставил сообщение', 'system_name' => 'voice_message' ],
            [ 'name' => 'Перезвонить', 'system_name' => 'call_back_later' ],
            [ 'name' => 'Не на месте', 'system_name' => 'not_available' ],
            [ 'name' => 'Разговор', 'system_name' => 'contact' ],
            [ 'name' => 'Неверный номер', 'system_name' => 'wrong_number' ],
            [ 'name' => 'Не дозвонился', 'system_name' => 'did_not_get_through' ],
            [ 'name' => 'Занят', 'system_name' => 'number_is_busy' ],
            [ 'name' => 'Неизвестный', 'system_name' => 'unknown' ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ref_amo_call_statuses');
    }
}
