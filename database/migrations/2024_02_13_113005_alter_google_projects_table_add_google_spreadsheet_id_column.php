<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterGoogleProjectsTableAddGoogleSpreadsheetIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('google_projects', function (Blueprint $table) {
            $table->string('google_spreadsheet_id')->nullable()->after('google_spreadsheet');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('google_projects', ['google_spreadsheet_id']);
    }
}
