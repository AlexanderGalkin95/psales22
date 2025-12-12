<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBitrixTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bitrix_tokens', function (Blueprint $table) {
            $table->id();
            $table->integer('bitrix_code_id');
            $table->text('access_token');
            $table->bigInteger('expires');
            $table->timestamp('expires_in');
            $table->char('status', 2)->nullable();
            $table->text('refresh_token');
            $table->text('application_token');
            $table->timestamps();

            $table->foreign('bitrix_code_id','fk_bitrix_tokens_bitrix_codes_bitrix_code_id')
                ->references('id')->on('bitrix_codes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bitrix_tokens');
    }
}
