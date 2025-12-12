<?php

namespace App\Console\Commands;

use App\Services\HtmlToImageService;
use Illuminate\Console\Command;


class TestScreenshot2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'do:screen2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $report = [
            ['manager' => 'Фамилияфамилия Имяимяимяимя', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
            ['manager' => 'asdasd zxczxc', 'fg' => 23, 'crm' => 100],
        ];
        $creterias = [
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
            ['критерий', 100],
        ];

        $html = view('reports.daily_report_table', [
            'report' => $report,
            'criterias' => $creterias,
            'reportDate' => now()->format('d.m.Y'),
        ])->render();

        $service = new HtmlToImageService();
        $service->setHtml($html);
        $path = $service->handle();
        $this->line($path);

        //$service->delFiles();

        return 0;
    }
}
