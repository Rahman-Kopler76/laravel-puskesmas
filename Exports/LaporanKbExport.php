<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class LaporanKbExport implements FromView, ShouldAutoSize, WithEvents
{
    protected $data;
    protected $namaBulan;
    protected $tahun;

    public function __construct(array $data, $namaBulan, $tahun)
    {
        $this->data = $data;
        $this->namaBulan = $namaBulan;
        $this->tahun = $tahun;
    }

    public function view(): View
    {
        return view('pages.apps.pustu.keluarga_berencana.exports.laporan_kb', [
            'reportData' => $this->data,
            'namaBulan' => $this->namaBulan,
            'tahun' => $this->tahun
        ]);
    }

    // app/Exports/LaporanKbExport.php

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastColumn = 'W';

                // PERBAIKAN:
                // Header tabel utama (No, Nama Desa, dst.) dimulai dari baris ke-7.
                $startRow = 7;
                // Header tabel utama memiliki 3 baris (baris 7, 8, 9). Data mulai dari baris 10.
                $lastRow = 10 + count($this->data);

                // Terapkan border hanya pada tabel utama
                $cellRange = 'A' . $startRow . ':' . $lastColumn . $lastRow;
                $event->sheet->getDelegate()->getStyle($cellRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Atur style (bold, center) hanya untuk header tabel utama
                $headerRange = 'A' . $startRow . ':' . $lastColumn . '9';
                $event->sheet->getDelegate()->getStyle($headerRange)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle($headerRange)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $dataStartRow = 10; // Data dimulai dari baris ke-10
                $dataRange = 'C' . $dataStartRow . ':' . $lastColumn . $lastRow;
                $event->sheet->getDelegate()->getStyle($dataRange)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0;-#,##0;;@');
            },
        ];
    }
}
