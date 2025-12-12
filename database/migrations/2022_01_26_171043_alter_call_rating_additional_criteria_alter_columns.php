<?php

use App\Models\AdditionalCriteria;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCallRatingAdditionalCriteriaAlterColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('call_rating_additional_criteria', function (Blueprint $table) {
            $table->bigInteger('additional_criteria_option_id')->nullable()->change();
            $table->string('value')->nullable()->change();
        });

        Schema::table('additional_criteria', function (Blueprint $table) {
            $table->integer( 'index_number')->nullable();
        });
        AdditionalCriteria::all()->groupBy('project_id')->each(function ($item) {
            $index = 1;
            $item->each(function (AdditionalCriteria $field) use (&$index) {
                $field->update(['index_number' => $index++]);
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('additional_criteria', ['index_number']);
    }
}
