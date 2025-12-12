<?php

use App\Models\Project;
use App\Models\ProjectAssessor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectAssessorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_assessors', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('project_id');
            $table->bigInteger('user_id');
            $table->timestamps();

            $table->unique(['user_id', 'project_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
        });

        $data = Project::all()->map(function ($item) {
            return [
                'project_id' => $item->id,
                'user_id' => $item->assessor_id,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        })->toArray();

        ProjectAssessor::insert($data);

        Schema::table('projects', function (Blueprint $table) {
            $table->bigInteger('assessor_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_assessors');
    }
}
