<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableProjectsAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            //$table->dropColumn('amocrm_name');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->integer('amo_code_id')->unique()->nullable();  // This stays null until a widget is bound

            $table->foreign('amo_code_id', 'fk_projects_amo_codes_amo_code_id')
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
        Schema::table('projects', function (Blueprint $table) {

            if (Schema::hasColumn('projects', 'amo_code_id')) {
                $table->dropColumn('amo_code_id');
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            //$table->string('amocrm_name')->nullable();
        });
    }
}
