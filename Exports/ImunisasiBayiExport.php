<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ImunisasiBayiExport implements WithEvents, WithColumnWidths, WithTitle, WithColumnFormatting
{
    protected $data;

    public function __construct(Collection $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Data Imunisasi Bayi';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 20,
            'C' => 20,
            'D' => 15,
            'E' => 5,
            'F' => 20,
            'G' => 30,
            'H' => 20,
            'I' => 20,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'H' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // --- Definisi Style ---
                $titleStyle = [
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ];
                $posyanduTitleStyle = [
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ];
                $headerStyle = [
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ];
                $allBordersStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ];

                // --- JUDUL UTAMA ---
                $sheet->mergeCells('A1:I2');
                $sheet->setCellValue('A1', 'Nama-nama bayi yang di imunisasi');
                $sheet->getStyle('A1')->applyFromArray($titleStyle);
                $sheet->getStyle('A1')->getAlignment()->setWrapText(true);

                // --- DATA ---
                $rowNumber = 3;

                foreach ($this->data as $posyanduName => $bayiGroup) {
                    $sheet->mergeCells('A' . $rowNumber . ':I' . $rowNumber);
                    $sheet->setCellValue('A' . $rowNumber, 'Nama Posyandu: ' . $posyanduName);
                    $sheet->getStyle('A' . $rowNumber)->applyFromArray($posyanduTitleStyle);
                    $rowNumber++;

                    $startHeaderRow = $rowNumber;
                    $headers = ['No', 'Nama Bayi', 'Nama Orang tua', 'Tanggal Lahir', 'JK', 'Nama Imunisasi', 'Alamat Lengkap', 'NIK Orang Tua', 'NIK Balita'];
                    $sheet->fromArray($headers, null, 'A' . $rowNumber);
                    $sheet->getStyle('A' . $rowNumber . ':I' . $rowNumber)->applyFromArray($headerStyle);
                    $rowNumber++;

                    $nomor = 1;
                    foreach ($bayiGroup as $bayi) {
                        $sheet->setCellValue('A' . $rowNumber, $nomor++);
                        $sheet->setCellValue('B' . $rowNumber, $bayi->nama_bayi);
                        $sheet->setCellValue('C' . $rowNumber, $bayi->nama_orang_tua);
                        $sheet->setCellValue('D' . $rowNumber, $bayi->tanggal_lahir);
                        $sheet->setCellValue('E' . $rowNumber, $bayi->jenis_kelamin);

                        // PERBAIKAN: Mengambil nama imunisasi dari relasi
                        $sheet->setCellValue('F' . $rowNumber, $bayi->jenisImunisasi->nama_imunisasi ?? 'N/A');

                        $sheet->setCellValue('G' . $rowNumber, $bayi->alamat_lengkap);
                        $sheet->setCellValueExplicit('H' . $rowNumber, $bayi->nik_orang_tua, DataType::TYPE_STRING);
                        $sheet->setCellValueExplicit('I' . $rowNumber, $bayi->nik_bayi, DataType::TYPE_STRING);

                        $rowNumber++;
                    }

                    $endDataRow = $rowNumber - 1;
                    $sheet->getStyle('A' . $startHeaderRow . ':I' . $endDataRow)->applyFromArray($allBordersStyle);
                    $rowNumber += 2;
                }
            },
        ];
    }
}