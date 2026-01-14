<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class LaporanImunisasiExport implements FromView, ShouldAutoSize, WithEvents
{
    protected $data;
    protected $bulan;
    protected $tahun;

    public function __construct(array $data, $bulan, $tahun)
    {
        $this->data = $data;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function view(): View
    {
        return view('pages.apps.pustu.imunisasi.exports.laporan_imunisasi', [
            'reportData' => $this->data,
            'namaBulan' => Carbon::create()->month($this->bulan)->translatedFormat('F'),
            'tahun' => $this->tahun
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Header laporan berada di baris 5, 6, 7. Data mulai dari baris 8.
                // Jadi, baris terakhir adalah 7 + jumlah baris data.
                $lastRow = 13 + count($this->data);

                // PERBAIKAN: Lebarkan range sel dari AC ke AF
                $cellRange = 'A5:AF' . $lastRow;
                $event->sheet->getDelegate()->getStyle($cellRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Style untuk header utama (baris 5, 6, dan 7)
                // PERBAIKAN: Lebarkan juga range header dari AC ke AF
                $headerRange = 'A5:AF7';
                $event->sheet->getDelegate()->getStyle($headerRange)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle($headerRange)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $dataStartRow = 8; // Data dimulai dari baris ke-8
                $dataRange = 'C' . $dataStartRow . ':AF' . $lastRow;
                $event->sheet->getDelegate()->getStyle($dataRange)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0;-#,##0;;@');
            },
        ];
    }
}
