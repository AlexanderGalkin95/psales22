<?php

use App\Models\BitrixCallStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBitrixCallStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ref_bitrix_call_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('system_name')->default('500');
        });

        BitrixCallStatus::insert([
            [ 'name' => 'Успешный звонок', 'system_name' => '200' ],
            [ 'name' => 'Пропущенный звонок', 'system_name' => '304' ],
            [ 'name' => 'Отклонено', 'system_name' => '603' ],
            [ 'name' => 'Вызов отменен', 'system_name' => '603-S' ],
            [ 'name' => 'Запрещено', 'system_name' => '403' ],
            [ 'name' => 'Неверный номер', 'system_name' => '404' ],
            [ 'name' => 'Занято', 'system_name' => '486' ],
            [ 'name' => 'Данное направление не доступно', 'system_name' => '484' ],
            [ 'name' => 'Данное направление не доступно', 'system_name' => '403' ],
            [ 'name' => 'Временно не доступен', 'system_name' => '480' ],
            [ 'name' => 'Недостаточно средств на счету', 'system_name' => '402' ],
            [ 'name' => 'Заблокировано', 'system_name' => '423' ],
            [ 'name' => 'Внутренняя ошибка сервера', 'system_name' => '500' ],
            [ 'name' => 'Не определен', 'system_name' => '500' ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ref_bitrix_call_statuses');
    }
}
