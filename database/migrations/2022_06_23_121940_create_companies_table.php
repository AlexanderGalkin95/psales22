<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('niche');
            $table->string('domain')->nullable();
            $table->boolean('active')->default(true);
            $table->string('logo')->nullable();
            $table->bigInteger('admin_id');
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->bigInteger('contact_tariff');
            $table->string('contact_agreement');
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
