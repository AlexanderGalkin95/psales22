<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HtmlToImageService
{

    private $disk;
    private ?string $html;

    private ?string $diskFilePathPdf = null;
    private ?string $diskFilePathJpg = null;


    public function __construct(?string $html = null)
    {
        $this->disk = Storage::disk('tmp');
        $this->html = $html;
    }


    private function genFilename(): string
    {
        return time() . Str::random(4);
    }


    public function setHtml(string $html): void
    {
        $this->html = $html;
    }

    public function delFiles()
    {
        $this->disk->delete($this->diskFilePathPdf);
        $this->disk->delete($this->diskFilePathJpg);
    }

    public function handle(): string
    {
        // увеличен лист, чтоб влезало больше строк данных на одну страницу.
        // также можно было оставить А4, уменьшив шрифт и лого в шаблоне html
        // imageMagic по-умолчанию конвертит в картинку только последнюю страницу
        //$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        //$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, 'A2', true, 'UTF-8', false);
        // A2 увеличен по высоте в 2 раза, т.к. иногда не влазят менеджеры
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, 'mm', [420, 594 * 2], true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
//        $pdf->SetAuthor('Nicola Asuni');
//        $pdf->SetTitle('TCPDF Example 001');
//        $pdf->SetSubject('TCPDF Tutorial');
//        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        $pdf->setRightMargin(200);
        $pdf->setHeaderMargin(0);
        $pdf->setFooterMargin(0);

        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        //$pdf->setFontSubsetting(false);

        // нет кириллицы
        //$pdf->SetFont('helvetica', 'B', 12);
        //$pdf->SetFont('times', '', 12);
        //$pdf->SetFont('cid0jp', '', 12);
        //$pdf->SetFont('helvetica', '', 12);
        //$pdf->SetFont('pdfacourier', '', 12);
        //$pdf->SetFont('aefurat', '', 12);
        //$pdf->SetFont('courier', '', 12);

        // подходящие
        //$pdf->SetFont('dejavuserif', '', 12);
        //$pdf->SetFont('dejavusans', '', 12);
        //$pdf->SetFont('dejavusanscondensed', '', 12);
        //$pdf->SetFont('dejavusansextralight', '', 12);
        //$pdf->SetFont('dejavusansmono', '', 12);
        //$pdf->SetFont('freemono', '', 12);
        //$pdf->SetFont('freesans', '', 12);
        //$pdf->SetFont('freeserif', '', 12);
        $pdf->SetFont('freesans', '', 12);

        $pdf->AddPage();

        $pdf->writeHTML($this->html, true, false, true, false, '');
        $filename = $this->genFilename();
        $basenamePdf = "$filename.pdf";
        $basenameJpg = "$filename.jpg";

        $dirPath = $this->disk->path('');
        $filePathPdf = $dirPath . $basenamePdf;
        $filePathJpg = $dirPath . $basenameJpg;

        $pdf->Output($filePathPdf, 'F');
        $this->diskFilePathPdf = $basenamePdf;

        $imagick = new \Imagick();
        // это разрешение(плотность) точек, а не разрешение в классическом понимании.
        // По умолчанию 72. Увеличиваем до 108.
        $imagick->setResolution(108, 108);
        $imagick->readImage($filePathPdf);
        $imagick->trimImage(0);
        $imagick->borderImage('white', 20, 20);
        $imagick->writeImage($filePathJpg);
        $this->diskFilePathJpg = $basenameJpg;

        return $filePathJpg;
    }
}
