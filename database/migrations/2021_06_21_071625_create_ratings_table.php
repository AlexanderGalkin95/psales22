<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('system_name');
            $table->timestamps();
        });

        DB::table('ratings')->insert([
            [
                'name' => 'Бинарная',
                'system_name' => 'binary',
            ],
            [
                'name' => 'Тройная',
                'system_name' => 'ternary',
            ],
        ]);

        Schema::table('projects', function (Blueprint $table) {
            $table->integer('rating_id')->nullable();

            $table->foreign('rating_id','fk_projects_ratings_rating_id')
                ->references('id')->on('ratings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropColumns('projects', ['rating_id']);
        Schema::dropIfExists('ratings');
    }
}
