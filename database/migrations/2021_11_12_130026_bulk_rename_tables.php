<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BulkRenameTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->rename('ref_ratings');
        });
        Schema::table('project_crm_objections', function (Blueprint $table) {
            $table->rename('project_objection_fields');
        });
        Schema::table('call_rating', function (Blueprint $table) {
            $table->rename('call_ratings');
        });
        Schema::table('call_types', function (Blueprint $table) {
            $table->rename('project_call_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ref_ratings', function (Blueprint $table) {
            $table->rename('ratings');
        });
        Schema::table('project_objection_fields', function (Blueprint $table) {
            $table->rename('project_crm_objections');
        });
        Schema::table('call_ratings', function (Blueprint $table) {
            $table->rename('call_rating');
        });
        Schema::table('project_call_types', function (Blueprint $table) {
            $table->rename('call_types');
        });
    }
}
