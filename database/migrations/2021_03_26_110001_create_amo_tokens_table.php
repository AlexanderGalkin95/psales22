<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_tokens', function (Blueprint $table) {
            $table->id();
            $table->text('access_token');
            $table->timestamp('expires_in');
            $table->text('refresh_token');
            $table->string('token_type')->default('Bearer');
            $table->integer('amo_code_id');
            $table->timestamps();

            $table->foreign('amo_code_id','fk_amo_tokens_amo_codes_amo_code_id')
                ->references('id')->on('amo_codes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amo_tokens');
    }
}
