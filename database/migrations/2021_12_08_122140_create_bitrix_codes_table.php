<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBitrixCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bitrix_codes', function (Blueprint $table) {
            $table->id();
            $table->string('client_id')->unique();
            $table->string('client_secret')->nullable();
            $table->boolean('is_webhook')->default(false);
            $table->string('domain')->unique();
            $table->text('webhook_url')->nullable();
            $table->longText('scope');
            $table->string('server_endpoint');
            $table->string('client_endpoint');
            $table->string('member_id');
            $table->integer('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bitrix_codes');
    }
}
