<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProjectCrmFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_crm_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('google_column');
            $table->integer('project_id')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects');
        });

        DB::table('project_crm_fields')->insert([
             [ 'name' => 'Примечание', 'google_column' => 'AY' ],
             [ 'name' => 'Задача', 'google_column' => 'AZ' ],
             [ 'name' => 'Статус', 'google_column' => 'BA' ],
             [ 'name' => 'Причина отказа', 'google_column' => 'BB' ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_crm_fields');
    }
}
