<?php

use App\Models\RefIntegration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefIntegrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ref_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('system_name', 100);
            $table->string('type');
            $table->string('validator');
            $table->timestamps();
        });

        RefIntegration::insert([
            [
                'name' => 'AmoCRM',
                'system_name' => 'amo_crm',
                'type' => \App\Models\AmoCode::class,
                'validator' => '^([0-9a-z-_]+)\.amocrm\.ru$',
            ],
            [
                'name' => 'Bitrix24',
                'system_name' => 'bitrix_24',
                'type' => \App\Models\BitrixCode::class,
                'validator' => '^([0-9a-z-_]+)\.bitrix24\.ru$',
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
        Schema::dropIfExists('ref_integrations');
    }
}
