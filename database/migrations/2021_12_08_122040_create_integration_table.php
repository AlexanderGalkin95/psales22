<?php

use App\Models\AmoCode;
use App\Models\Integration;
use App\Models\RefIntegration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegrationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->integer('integration_id');
            $table->integer('ref_integration_id');
            $table->timestamps();

            $table->unique(['id', 'integration_id', 'ref_integration_id']);
        });

        $types = RefIntegration::all();
        AmoCode::all()->each(function ($amo) use ($types) {
            Integration::create([
                'integration_id' => $amo->id,
                'ref_integration_id' => $types->firstWhere('system_name', 'amo_crm')->id,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('integrations');
    }
}
