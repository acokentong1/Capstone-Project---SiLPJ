<?php

namespace App\Exports;

use App\Support\RekapQuery;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RekapExport extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithColumnFormatting,
    WithCustomValueBinder
{
    public function __construct(
        private int $schoolId,
        private array $filters,
        private string $schoolName,
    ) {}

    public function collection()
    {
        return RekapQuery::query($this->schoolId, $this->filters)
            ->orderBy('tanggal')
            ->orderBy('id')
            ->get(['tanggal', 'uraian', 'kategori', 'total']);
    }

    public function bindValue(Cell $cell, $value): bool
    {
        // Text originating from users must never become an Excel formula.
        if (in_array($cell->getColumn(), ['A', 'B', 'C'], true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell, $value);
    }

    public function headings(): array
    {
        return ['Tanggal', 'Uraian', 'Kategori', 'Total'];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->insertNewRowBefore(1, 3);
        $sheet->setCellValue('A1', 'REKAP KEUANGAN SILPJ');
        $sheet->setCellValueExplicit(
            'A2',
            $this->schoolName,
            DataType::TYPE_STRING,
        );
        $sheet->setCellValue('A3', 'Tahun Anggaran ' . $this->filters['tahun']);
        foreach ([1, 2, 3] as $row) {
            $sheet->mergeCells("A{$row}:D{$row}");
        }
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2:A3')->getFont()->setBold(true);
        $sheet->getStyle('A4:D4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => '2563EB'],
            ],
            'alignment' => ['horizontal' => 'center'],
        ]);
        $lastRow = max(4, $sheet->getHighestRow());
        $sheet
            ->getStyle("A4:D{$lastRow}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle('thin');
        $sheet->freezePane('A5');
        foreach (range('A', 'D') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    public function columnFormats(): array
    {
        return ['D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1];
    }
}
