<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class LaporanSurveilansExport implements FromView, WithEvents, WithColumnWidths
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
        return view('pages.apps.pustu.penyakit.exports.laporan_surveilans', [
            'reportData' => $this->data,
            'namaBulan' => $this->namaBulan,
            'tahun' => $this->tahun
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 30,  // Penyakit
            'C' => 5,
            'D' => 5,
            'E' => 5,
            'F' => 5,
            'G' => 5,
            'H' => 5,
            'I' => 5,
            'J' => 5,
            'K' => 5,
            'L' => 5,
            'M' => 5,
            'N' => 5,
            'O' => 5,
            'P' => 5,
            'Q' => 5,
            'R' => 5,
            'S' => 5,
            'T' => 5,
            'U' => 5,
            'V' => 5,
            'W' => 5,
            'X' => 5,
            'Y' => 5,
            'Z' => 5,
            'AA' => 8, // Total Laki
            'AB' => 8, // Total Perp
            'AC' => 15, // Total Kunjungan
        ];
    }

    // PERBAIKAN LOGIKA ADA DI DALAM METHOD INI
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $lastColumn = 'AC';
                $startRow = 8; // Tabel utama dimulai dari baris 8

                // PERBAIKAN: Perhitungan baris terakhir yang benar
                // Header (3 baris) + Jumlah data penyakit + Baris Total (1 baris)
                // Baris terakhir = 8 (start) + 3 (header) + count($this->data) + 1 (total) - 1 (karena startRow sudah dihitung)
                $lastRow = 12 + count($this->data);

                // Terapkan border ke seluruh tabel utama, termasuk baris TOTAL
                $cellRange = 'A' . $startRow . ':' . $lastColumn . $lastRow;
                $event->sheet->getDelegate()->getStyle($cellRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Atur style header
                $headerRange = 'A' . $startRow . ':' . $lastColumn . ($startRow + 2); // Header sekarang 3 baris
                $event->sheet->getDelegate()->getStyle($headerRange)->getFont()->setBold(true);
                $event->sheet->getDelegate()->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle($headerRange)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                // PERBAIKAN: Terapkan format angka ke seluruh area data, termasuk baris TOTAL
                $dataStartRow = $startRow + 3; // Data dimulai setelah 3 baris header
                $dataRange = 'C' . $dataStartRow . ':' . $lastColumn . $lastRow;
                $event->sheet->getDelegate()->getStyle($dataRange)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0;-#,##0;;@'); // Format untuk menyembunyikan nol
            },
        ];
    }
}
