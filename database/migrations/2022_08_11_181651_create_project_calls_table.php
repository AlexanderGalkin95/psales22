<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Call;
use App\Models\ProjectCall;

class CreateProjectCallsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_calls', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('project_id');
            $table->bigInteger('call_id');
            $table->timestamps();

            $table->unique(['project_id', 'call_id']);

            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('call_id')->references('id')->on('calls');
        });

        $this->reassignValues();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_calls');
    }

    private function reassignValues()
    {
        $callData = Call::whereNotNull('project_id')
            ->get()
            ->transform(function ($item) {
                return [
                    'project_id' => $item->project_id,
                    'call_id' => $item->id,
                    'created_at' => DB::raw('now()')
                ];
            })
            ->toArray();

        ProjectCall::insert(array_values($callData));
    }
}
