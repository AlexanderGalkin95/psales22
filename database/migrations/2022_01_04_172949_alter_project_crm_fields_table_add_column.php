<?php

use App\Models\ProjectCrmField;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProjectCrmFieldsTableAddColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_crm_fields', function (Blueprint $table) {
            $table->integer( 'index_number')->nullable();
        });

        ProjectCrmField::all()->groupBy('project_id')->each(function ($item) {
            $index = 1;
            $item->each(function (ProjectCrmField $field) use (&$index) {
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
        Schema::dropColumns('project_crm_fields', ['index_number']);
    }
}
