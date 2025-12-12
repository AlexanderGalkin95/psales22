<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterGoogleProjectsTableFillTelegramBotIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $data = DB::table('telegram_bot')->select('id', 'project_id')->get();
        foreach ($data as $row) {
            DB::table('google_projects')
                ->where('id', $row->project_id)
                ->update(['telegram_bot_id' => $row->id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('google_projects')->update(['telegram_bot_id' => null]);
    }
}
