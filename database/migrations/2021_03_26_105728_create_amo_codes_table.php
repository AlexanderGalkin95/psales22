<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmoCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amo_codes', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->string('widget');
            $table->text('code');
            $table->string('domain')->unique();
            $table->string('client_id')->unique();
            $table->string('client_secret')->nullable();
            $table->integer('test')->default(0);
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
        Schema::dropIfExists('amo_codes');
    }
}
