<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterGoogleProjectsTableFillColumnOverrideReportSentAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $data = DB::table('telegram_bot_reports')
            ->whereNotNull('last_report_sent_at')
            ->select('project_id', DB::raw('MAX(last_report_sent_at) as last_report_sent_at'))
            ->groupBy('project_id')
            ->get();

        foreach ($data as $row) {
            DB::table('google_projects')
                ->where('id', $row->project_id)
                ->update(['override_report_sent_at' => $row->last_report_sent_at]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('google_projects')->update(['override_report_sent_at' => null]);
    }
}
