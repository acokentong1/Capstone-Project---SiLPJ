<?php

namespace App\Http\Controllers;

use App\Models\Belanja;
use App\Models\SchoolProfile;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanLpjController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->resolveFilters($request);

        $user = auth()->user();

        $school = null;

        if ($user && ($user->school_profile_id ?? 0)) {
            $school = SchoolProfile::find($user->school_profile_id ?? 0);
        }
        $belanjas = $this->filteredQuery($filters)
            ->with(['notaPesanan', 'kwitansi', 'bapb'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $stats = $this->buildStats($belanjas);
        $periodeLabel = $this->periodeLabel($filters);
        $hasFilters = collect($filters)->contains(fn($value) => filled($value));

        $tahunAnggaran = Belanja::where(
            'school_profile_id',
            auth()->user()->school_profile_id ?? 0,
        )
            ->whereNotNull('tahun_anggaran')
            ->select('tahun_anggaran')
            ->distinct()
            ->orderByDesc('tahun_anggaran')
            ->pluck('tahun_anggaran');

        return view(
            'laporan-lpj.index',
            compact(
                'school',
                'belanjas',
                'stats',
                'filters',
                'periodeLabel',
                'hasFilters',
                'tahunAnggaran',
            ),
        );
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $this->resolveFilters($request);
        $user = auth()->user();

        $school = null;

        if ($user && ($user->school_profile_id ?? 0)) {
            $school = SchoolProfile::find($user->school_profile_id ?? 0);
        }

        $belanjas = $this->filteredQuery($filters)
            ->with(['notaPesanan', 'kwitansi', 'bapb'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $stats = $this->buildStats($belanjas);
        $periodeLabel = $this->periodeLabel($filters);
        $filename = 'laporan-lpj-' . now()->format('Ymd-His') . '.csv';

        ActivityLogger::record(
            (int) auth()->id(),
            'export',
            'Mengekspor Laporan LPJ ke CSV',
            metadata: [
                'jumlah_transaksi' => $stats['jumlahTransaksi'],
                'periode' => $periodeLabel,
            ],
        );

        return response()->streamDownload(
            function () use ($school, $belanjas, $stats, $periodeLabel) {
                $handle = fopen('php://output', 'w');

                // UTF-8 BOM agar karakter Indonesia terbaca baik saat dibuka di Excel.
                fwrite($handle, "\xEF\xBB\xBF");

                $this->csvRow($handle, ['LAPORAN PERTANGGUNGJAWABAN (LPJ)']);
                $this->csvRow($handle, [
                    'Sekolah',
                    $school?->nama_sekolah ?? '-',
                ]);
                $this->csvRow($handle, ['NPSN', $school?->npsn ?? '-']);
                $this->csvRow($handle, ['Periode', $periodeLabel]);
                $this->csvRow($handle, []);
                $this->csvRow($handle, ['RINGKASAN']);
                $this->csvRow($handle, [
                    'Jumlah Transaksi',
                    $stats['jumlahTransaksi'],
                ]);
                $this->csvRow($handle, [
                    'Total Belanja',
                    $stats['totalBelanja'],
                ]);
                $this->csvRow($handle, [
                    'LPJ Lengkap',
                    $stats['jumlahLengkap'],
                ]);
                $this->csvRow($handle, [
                    'LPJ Dalam Proses',
                    $stats['jumlahProses'],
                ]);
                $this->csvRow($handle, [
                    'Belum Ada Dokumen',
                    $stats['jumlahBelumAdaDokumen'],
                ]);
                $this->csvRow($handle, [
                    'Kelengkapan Dokumen',
                    $stats['persenKelengkapanDokumen'] . '%',
                ]);
                $this->csvRow($handle, [
                    'Belanja Barang',
                    $stats['totalBarang'],
                ]);
                $this->csvRow($handle, ['Belanja Jasa', $stats['totalJasa']]);
                $this->csvRow($handle, []);

                $this->csvRow($handle, [
                    'No',
                    'Tanggal',
                    'No. Bukti',
                    'Kategori',
                    'Uraian',
                    'Jumlah',
                    'Harga Satuan',
                    'Total',
                    'Nota Pesanan',
                    'Kwitansi',
                    'BAPB',
                    'Status LPJ',
                ]);

                foreach ($belanjas as $index => $item) {
                    $jumlahDokumen = $this->documentCount($item);
                    $status =
                        $jumlahDokumen === 3
                            ? 'Lengkap'
                            : ($jumlahDokumen === 0
                                ? 'Belum ada dokumen'
                                : "Proses {$jumlahDokumen}/3");

                    $this->csvRow($handle, [
                        $index + 1,
                        Carbon::parse($item->tanggal)->format('d-m-Y'),
                        $item->nomor_bukti ?? '-',
                        $item->kategori ?? '-',
                        $item->uraian,
                        $item->jumlah,
                        $item->harga_satuan,
                        $item->total,
                        $item->notaPesanan ? 'Ada' : 'Belum',
                        $item->kwitansi ? 'Ada' : 'Belum',
                        $item->bapb ? 'Ada' : 'Belum',
                        $status,
                    ]);
                }

                $this->csvRow($handle, []);
                $this->csvRow($handle, [
                    'TOTAL KESELURUHAN',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $stats['totalBelanja'],
                ]);

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ],
        );
    }

    /**
     * Export laporan LPJ ke Excel (.xlsx) tanpa mengganggu fitur CSV.
     * Data serta filter memakai query yang sama dengan halaman Laporan LPJ.
     */
    public function exportExcel(Request $request): BinaryFileResponse
    {
        $filters = $this->resolveFilters($request);
        $user = auth()->user();
        $school = $user?->school_profile_id
            ? SchoolProfile::find($user->school_profile_id ?? 0)
            : null;

        $belanjas = $this->filteredQuery($filters)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $stats = $this->buildStats($belanjas);
        $periodeLabel = $this->periodeLabel($filters);
        $tahunLabel = $filters['tahun'] ?: 'Semua Tahun';
        $filename = 'laporan-lpj-' . now()->format('Ymd-His') . '.xlsx';

        // Selesaikan file sebelum header HTTP dikirim agar exception dapat ditangani Laravel.
        $temporaryFile = tempnam(sys_get_temp_dir(), 'silpj-lpj-');
        if ($temporaryFile === false) {
            throw new \RuntimeException(
                'Tidak dapat membuat file sementara untuk ekspor Excel.',
            );
        }
        $workbook = null;
        try {
            $workbook = new Spreadsheet();
            $sheet = $workbook->getActiveSheet();
            $sheet->setTitle('Laporan LPJ');
            $sheet->setShowGridlines(false);
            $sheet->getDefaultRowDimension()->setRowHeight(19);
            $workbook
                ->getDefaultStyle()
                ->getFont()
                ->setName('Aptos')
                ->setSize(10);

            // Lebar kolom yang proporsional dan mudah dibaca di Excel.
            foreach (
                [
                    'A' => 7,
                    'B' => 16,
                    'C' => 16,
                    'D' => 15,
                    'E' => 36,
                    'F' => 11,
                    'G' => 19,
                    'H' => 20,
                    'I' => 16,
                    'J' => 16,
                    'K' => 13,
                    'L' => 24,
                ]
                as $column => $width
            ) {
                $sheet->getColumnDimension($column)->setWidth($width);
            }

            // Identitas laporan dan sekolah.
            $sheet->mergeCells('A1:L2');
            $sheet->setCellValue('A1', 'LAPORAN PERTANGGUNGJAWABAN (LPJ)');
            $sheet->getRowDimension(1)->setRowHeight(25);
            $sheet->getRowDimension(2)->setRowHeight(20);
            $sheet->getStyle('A1:L2')->applyFromArray([
                'font' => [
                    'name' => 'Aptos Display',
                    'size' => 17,
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1D4ED8'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            $sheet->mergeCells('A3:L3');
            $this->writeExcelText(
                $sheet,
                'A3',
                $school?->nama_sekolah ?? 'Sekolah tidak diketahui',
            );
            $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(13);
            $sheet
                ->getStyle('A3')
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells('A4:L4');
            $this->writeExcelText(
                $sheet,
                'A4',
                'NPSN: ' . ($school?->npsn ?? '-'),
            );
            $sheet
                ->getStyle('A4')
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells('A5:L5');
            $this->writeExcelText(
                $sheet,
                'A5',
                'Periode: ' .
                    $periodeLabel .
                    '  |  Tahun Anggaran: ' .
                    $tahunLabel,
            );
            $sheet
                ->getStyle('A5')
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet
                ->getStyle('A5')
                ->getFont()
                ->setItalic(true)
                ->getColor()
                ->setRGB('64748B');

            // Ringkasan: nilai dan jumlah diambil dari transaksi yang sudah difilter.
            $sheet->mergeCells('A7:L7');
            $sheet->setCellValue('A7', 'RINGKASAN LAPORAN');
            $sheet->getStyle('A7:L7')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E40AF'],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension(7)->setRowHeight(24);

            $summary = [
                8 => [
                    'Jumlah Transaksi',
                    $stats['jumlahTransaksi'],
                    'Total Belanja',
                    $stats['totalBelanja'],
                ],
                9 => [
                    'LPJ Lengkap',
                    $stats['jumlahLengkap'],
                    'LPJ Dalam Proses',
                    $stats['jumlahProses'],
                ],
                10 => [
                    'Belum Ada Dokumen',
                    $stats['jumlahBelumAdaDokumen'],
                    'Kelengkapan Dokumen',
                    $stats['persenKelengkapanDokumen'] . '%',
                ],
                11 => [
                    'Belanja Barang',
                    $stats['totalBarang'],
                    'Belanja Jasa',
                    $stats['totalJasa'],
                ],
                12 => [
                    'Nota Pesanan Ada',
                    $stats['jumlahNota'],
                    'Kwitansi Ada',
                    $stats['jumlahKwitansi'],
                ],
                13 => [
                    'BAPB Ada',
                    $stats['jumlahBapb'],
                    'LPJ Belum Lengkap',
                    $stats['jumlahBelumLengkap'],
                ],
            ];

            foreach (
                $summary
                as $row => [$leftLabel, $leftValue, $rightLabel, $rightValue]
            ) {
                $sheet->mergeCells("A{$row}:D{$row}");
                $sheet->mergeCells("E{$row}:F{$row}");
                $sheet->mergeCells("G{$row}:J{$row}");
                $sheet->mergeCells("K{$row}:L{$row}");
                $sheet->setCellValue("A{$row}", $leftLabel);
                $sheet->setCellValue("E{$row}", $leftValue);
                $sheet->setCellValue("G{$row}", $rightLabel);
                $sheet->setCellValue("K{$row}", $rightValue);
                $sheet
                    ->getStyle("A{$row}:L{$row}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB($row % 2 === 0 ? 'EFF6FF' : 'FFFFFF');
                $sheet
                    ->getStyle("E{$row}:F{$row}")
                    ->getFont()
                    ->setBold(true);
                $sheet
                    ->getStyle("K{$row}:L{$row}")
                    ->getFont()
                    ->setBold(true);
            }
            $currency = '"Rp" #,##0';
            foreach (['K8:L8', 'E11:F11', 'K11:L11'] as $range) {
                $sheet
                    ->getStyle($range)
                    ->getNumberFormat()
                    ->setFormatCode($currency);
            }
            $sheet
                ->getStyle('A8:L13')
                ->getBorders()
                ->getBottom()
                ->setBorderStyle(Border::BORDER_HAIR);

            $sheet->mergeCells('A15:L15');
            $sheet->setCellValue('A15', 'DETAIL TRANSAKSI BELANJA');
            $sheet->getStyle('A15:L15')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E40AF'],
                ],
            ]);
            $sheet->getRowDimension(15)->setRowHeight(24);

            $headerRow = 17;
            $headers = [
                'No',
                'Tanggal',
                'No. Bukti',
                'Kategori',
                'Uraian',
                'Jumlah',
                'Harga Satuan',
                'Total',
                'Nota Pesanan',
                'Kwitansi',
                'BAPB',
                'Status LPJ',
            ];
            foreach ($headers as $index => $heading) {
                $sheet->setCellValue(chr(65 + $index) . $headerRow, $heading);
            }
            $sheet->getRowDimension($headerRow)->setRowHeight(30);
            $sheet->getStyle("A{$headerRow}:L{$headerRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'wrapText' => true,
                ],
            ]);

            $dataStart = $headerRow + 1;
            $row = $dataStart;
            foreach ($belanjas as $index => $item) {
                $jumlahDokumen = $this->documentCount($item);
                $status =
                    $jumlahDokumen === 3
                        ? 'Lengkap'
                        : ($jumlahDokumen === 0
                            ? 'Belum ada dokumen'
                            : "Proses {$jumlahDokumen}/3");

                $sheet->setCellValue("A{$row}", $index + 1);
                $sheet->setCellValue(
                    "B{$row}",
                    ExcelDate::PHPToExcel(Carbon::parse($item->tanggal)),
                );
                $this->writeExcelText(
                    $sheet,
                    "C{$row}",
                    (string) ($item->nomor_bukti ?? '-'),
                );
                $this->writeExcelText(
                    $sheet,
                    "D{$row}",
                    (string) ($item->kategori ?? '-'),
                );
                $this->writeExcelText(
                    $sheet,
                    "E{$row}",
                    (string) $item->uraian,
                );
                $sheet->setCellValue("F{$row}", (int) $item->jumlah);
                $sheet->setCellValue("G{$row}", (float) $item->harga_satuan);
                $sheet->setCellValue("H{$row}", (float) $item->total);
                $sheet->setCellValue(
                    "I{$row}",
                    $item->notaPesanan ? 'Ada' : 'Belum',
                );
                $sheet->setCellValue(
                    "J{$row}",
                    $item->kwitansi ? 'Ada' : 'Belum',
                );
                $sheet->setCellValue("K{$row}", $item->bapb ? 'Ada' : 'Belum');
                $sheet->setCellValue("L{$row}", $status);

                $sheet
                    ->getStyle("B{$row}")
                    ->getNumberFormat()
                    ->setFormatCode('dd-mm-yyyy');
                $sheet
                    ->getStyle("G{$row}:H{$row}")
                    ->getNumberFormat()
                    ->setFormatCode($currency);
                $sheet
                    ->getStyle("E{$row}")
                    ->getAlignment()
                    ->setWrapText(true);
                if ($index % 2 === 1) {
                    $sheet
                        ->getStyle("A{$row}:L{$row}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB('F8FAFC');
                }
                $sheet
                    ->getStyle("L{$row}")
                    ->getFont()
                    ->getColor()
                    ->setRGB($jumlahDokumen === 3 ? '15803D' : 'B45309');
                $row++;
            }

            $lastDataRow = $row - 1;
            if ($belanjas->isEmpty()) {
                $sheet->mergeCells("A{$row}:L{$row}");
                $sheet->setCellValue(
                    "A{$row}",
                    'Tidak ada transaksi untuk filter yang dipilih.',
                );
                $sheet
                    ->getStyle("A{$row}:L{$row}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $lastDataRow = $row;
                $row++;
            } else {
                $sheet->setAutoFilter("A{$headerRow}:L{$lastDataRow}");
            }

            $tableLastRow = $row - 1;
            $sheet
                ->getStyle("A{$headerRow}:L{$tableLastRow}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);
            $sheet
                ->getStyle("F{$dataStart}:H{$tableLastRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet
                ->getStyle("A{$dataStart}:D{$tableLastRow}")
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER);

            $totalRow = $row + 1;
            $sheet->mergeCells("A{$totalRow}:G{$totalRow}");
            $sheet->setCellValue("A{$totalRow}", 'TOTAL KESELURUHAN');
            $sheet->setCellValue(
                "H{$totalRow}",
                $belanjas->isEmpty()
                    ? 0
                    : "=SUM(H{$dataStart}:H{$lastDataRow})",
            );
            $sheet
                ->getStyle("H{$totalRow}")
                ->getNumberFormat()
                ->setFormatCode($currency);
            $sheet->getStyle("A{$totalRow}:L{$totalRow}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DBEAFE'],
                ],
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '1D4ED8'],
                    ],
                ],
            ]);
            $sheet->getRowDimension($totalRow)->setRowHeight(24);

            // Kolom tanda tangan untuk laporan sekolah.
            $signatureRow = $totalRow + 3;
            $sheet->mergeCells("B{$signatureRow}:E{$signatureRow}");
            $sheet->mergeCells("H{$signatureRow}:K{$signatureRow}");
            $sheet->setCellValue(
                "B{$signatureRow}",
                'Mengetahui, Kepala Sekolah',
            );
            $sheet->setCellValue("H{$signatureRow}", 'Bendahara');

            $nameRow = $signatureRow + 4;
            $sheet->mergeCells("B{$nameRow}:E{$nameRow}");
            $sheet->mergeCells("H{$nameRow}:K{$nameRow}");
            $this->writeExcelText(
                $sheet,
                "B{$nameRow}",
                $school?->kepala_sekolah ?? '-',
            );
            $this->writeExcelText(
                $sheet,
                "H{$nameRow}",
                $school?->bendahara ?? '-',
            );
            $sheet
                ->getStyle("B{$nameRow}:K{$nameRow}")
                ->getFont()
                ->setBold(true);

            $nipRow = $nameRow + 1;
            $sheet->mergeCells("B{$nipRow}:E{$nipRow}");
            $sheet->mergeCells("H{$nipRow}:K{$nipRow}");
            $this->writeExcelText(
                $sheet,
                "B{$nipRow}",
                'NIP: ' . ($school?->nip_kepala ?? '-'),
            );
            $this->writeExcelText(
                $sheet,
                "H{$nipRow}",
                'NIP: ' . ($school?->nip_bendahara ?? '-'),
            );
            foreach ([$signatureRow, $nameRow, $nipRow] as $signedRow) {
                $sheet
                    ->getStyle("B{$signedRow}:E{$signedRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet
                    ->getStyle("H{$signedRow}:K{$signedRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            $sheet->freezePane('A18');
            $sheet->getPageSetup()->setOrientation('landscape');
            $sheet->getPageSetup()->setPaperSize('9'); // A4
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);
            $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 17);
            $sheet->getPageMargins()->setLeft(0.3);
            $sheet->getPageMargins()->setRight(0.3);
            $sheet->getPageMargins()->setTop(0.5);
            $sheet->getPageMargins()->setBottom(0.5);
            $sheet
                ->getHeaderFooter()
                ->setOddFooter(
                    '&LCetak: ' .
                        now()->format('d-m-Y H:i') .
                        '&RHalaman &P / &N',
                );
            $sheet->getPageSetup()->setPrintArea("A1:L{$nipRow}");

            (new Xlsx($workbook))->save($temporaryFile);
            ActivityLogger::record(
                (int) auth()->id(),
                'export',
                'Mengekspor Laporan LPJ ke Excel (.xlsx)',
                metadata: [
                    'jumlah_transaksi' => $stats['jumlahTransaksi'],
                    'periode' => $periodeLabel,
                    'tahun' => $tahunLabel,
                ],
            );

            clearstatcache(true, $temporaryFile);
            return response()
                ->download($temporaryFile, $filename, [
                    'Content-Type' =>
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Cache-Control' => 'private, no-store',
                ])
                ->deleteFileAfterSend(true);
        } catch (\Throwable $exception) {
            if (is_file($temporaryFile)) {
                unlink($temporaryFile);
            }
            throw $exception;
        } finally {
            $workbook?->disconnectWorksheets();
        }
    }

    /** Teks eksplisit mencegah formula injection dan menjaga nol awal nomor bukti/NPSN. */
    private function writeExcelText(
        Worksheet $sheet,
        string $cell,
        string $text,
    ): void {
        $sheet->setCellValueExplicit($cell, $text, DataType::TYPE_STRING);
    }

    private function filteredQuery(array $filters): Builder
    {
        $query = Belanja::query()
            ->where('school_profile_id', auth()->user()->school_profile_id ?? 0)
            ->with(['notaPesanan', 'kwitansi', 'bapb']);

        // Filter pencarian
        if (!empty($filters['q'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('uraian', 'like', '%' . $filters['q'] . '%')->orWhere(
                    'nomor_bukti',
                    'like',
                    '%' . $filters['q'] . '%',
                );
            });
        }

        // Filter kategori
        if (!empty($filters['kategori'])) {
            $query->where('kategori', $filters['kategori']);
        }

        // Filter tahun
        if (!empty($filters['tahun'])) {
            $query->where('tahun_anggaran', $filters['tahun']);
        }

        // Filter tanggal
        if (!empty($filters['tanggal_mulai'])) {
            $query->whereDate('tanggal', '>=', $filters['tanggal_mulai']);
        }

        if (!empty($filters['tanggal_selesai'])) {
            $query->whereDate('tanggal', '<=', $filters['tanggal_selesai']);
        }

        // Filter dokumen kurang
        if (!empty($filters['dokumen_kurang'])) {
            if ($filters['dokumen_kurang'] == 'nota') {
                $query->doesntHave('notaPesanan');
            }

            if ($filters['dokumen_kurang'] == 'kwitansi') {
                $query->doesntHave('kwitansi');
            }

            if ($filters['dokumen_kurang'] == 'bapb') {
                $query->doesntHave('bapb');
            }
        }

        // Filter status LPJ. Tiga status ini saling terpisah.
        if ($filters['status'] === 'lengkap') {
            $query
                ->whereHas('notaPesanan')
                ->whereHas('kwitansi')
                ->whereHas('bapb');
        } elseif ($filters['status'] === 'proses') {
            // Sedikitnya satu dokumen sudah ada, tetapi belum lengkap semuanya.
            $query
                ->where(function (Builder $builder) {
                    $builder
                        ->whereHas('notaPesanan')
                        ->orWhereHas('kwitansi')
                        ->orWhereHas('bapb');
                })
                ->where(function (Builder $builder) {
                    $builder
                        ->whereDoesntHave('notaPesanan')
                        ->orWhereDoesntHave('kwitansi')
                        ->orWhereDoesntHave('bapb');
                });
        } elseif ($filters['status'] === 'belum') {
            // Sama sekali belum ada ketiga dokumen LPJ.
            $query
                ->whereDoesntHave('notaPesanan')
                ->whereDoesntHave('kwitansi')
                ->whereDoesntHave('bapb');
        }

        return $query;
    }

    private function resolveFilters(Request $request): array
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'kategori' => ['nullable', 'in:Barang,Jasa'],
            'status' => ['nullable', 'in:lengkap,proses,belum'],
            'dokumen_kurang' => ['nullable', 'in:nota,kwitansi,bapb'],
            'tanggal_mulai' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_selesai' => ['nullable', 'date_format:Y-m-d'],
            'tahun' => ['nullable', 'integer', 'between:1900,9999'],
        ]);
        $q = trim((string) $request->query('q', ''));
        $kategori = in_array(
            $request->query('kategori'),
            ['Barang', 'Jasa'],
            true,
        )
            ? (string) $request->query('kategori')
            : '';
        $status = in_array(
            $request->query('status'),
            ['lengkap', 'proses', 'belum'],
            true,
        )
            ? (string) $request->query('status')
            : '';
        $dokumenKurang = in_array(
            $request->query('dokumen_kurang'),
            ['nota', 'kwitansi', 'bapb'],
            true,
        )
            ? (string) $request->query('dokumen_kurang')
            : '';
        $tanggalMulai = $this->normalizeDate($request->query('tanggal_mulai'));
        $tanggalSelesai = $this->normalizeDate(
            $request->query('tanggal_selesai'),
        );
        $tahun = preg_match('/^\d{4}$/', (string) $request->query('tahun'))
            ? (int) $request->query('tahun')
            : null;

        if (
            $tanggalMulai &&
            $tanggalSelesai &&
            $tanggalMulai > $tanggalSelesai
        ) {
            [$tanggalMulai, $tanggalSelesai] = [$tanggalSelesai, $tanggalMulai];
        }

        return [
            'q' => $q,
            'kategori' => $kategori,
            'status' => $status,
            'dokumen_kurang' => $dokumenKurang,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'tahun' => $tahun,
        ];
    }

    private function buildStats(Collection $belanjas): array
    {
        $jumlahTransaksi = $belanjas->count();
        $jumlahLengkap = 0;
        $jumlahProses = 0;
        $jumlahBelumAdaDokumen = 0;
        $jumlahNota = 0;
        $jumlahKwitansi = 0;
        $jumlahBapb = 0;
        $nilaiLpjLengkap = 0;

        foreach ($belanjas as $item) {
            $dokumen = $this->documentCount($item);

            $jumlahNota += $item->notaPesanan ? 1 : 0;
            $jumlahKwitansi += $item->kwitansi ? 1 : 0;
            $jumlahBapb += $item->bapb ? 1 : 0;

            if ($dokumen === 3) {
                $jumlahLengkap++;
                $nilaiLpjLengkap += (int) $item->total;
            } elseif ($dokumen === 0) {
                $jumlahBelumAdaDokumen++;
            } else {
                $jumlahProses++;
            }
        }

        $jumlahDokumenTersedia = $jumlahNota + $jumlahKwitansi + $jumlahBapb;
        $maksimumDokumen = $jumlahTransaksi * 3;

        return [
            'jumlahTransaksi' => $jumlahTransaksi,
            'totalBelanja' => (int) $belanjas->sum('total'),
            'jumlahLengkap' => $jumlahLengkap,
            'jumlahBelumLengkap' => max(0, $jumlahTransaksi - $jumlahLengkap),
            'jumlahProses' => $jumlahProses,
            'jumlahBelumAdaDokumen' => $jumlahBelumAdaDokumen,
            'jumlahNota' => $jumlahNota,
            'jumlahKwitansi' => $jumlahKwitansi,
            'jumlahBapb' => $jumlahBapb,
            'kurangNota' => max(0, $jumlahTransaksi - $jumlahNota),
            'kurangKwitansi' => max(0, $jumlahTransaksi - $jumlahKwitansi),
            'kurangBapb' => max(0, $jumlahTransaksi - $jumlahBapb),
            'persenLpjLengkap' =>
                $jumlahTransaksi > 0
                    ? (int) round(($jumlahLengkap / $jumlahTransaksi) * 100)
                    : 0,
            'persenKelengkapanDokumen' =>
                $maksimumDokumen > 0
                    ? (int) round(
                        ($jumlahDokumenTersedia / $maksimumDokumen) * 100,
                    )
                    : 0,
            'nilaiLpjLengkap' => $nilaiLpjLengkap,
            'jumlahBarang' => $belanjas->where('kategori', 'Barang')->count(),
            'jumlahJasa' => $belanjas->where('kategori', 'Jasa')->count(),
            'totalBarang' => (int) $belanjas
                ->where('kategori', 'Barang')
                ->sum('total'),
            'totalJasa' => (int) $belanjas
                ->where('kategori', 'Jasa')
                ->sum('total'),
        ];
    }

    private function documentCount(Belanja $belanja): int
    {
        return (int) (bool) $belanja->notaPesanan +
            (int) (bool) $belanja->kwitansi +
            (int) (bool) $belanja->bapb;
    }

    private function periodeLabel(array $filters): string
    {
        $mulai = $filters['tanggal_mulai'];
        $selesai = $filters['tanggal_selesai'];

        if ($mulai && $selesai) {
            if ($mulai === $selesai) {
                return Carbon::parse($mulai)->translatedFormat('d F Y');
            }

            return Carbon::parse($mulai)->translatedFormat('d F Y') .
                ' s.d. ' .
                Carbon::parse($selesai)->translatedFormat('d F Y');
        }

        if ($mulai) {
            return 'Sejak ' . Carbon::parse($mulai)->translatedFormat('d F Y');
        }

        if ($selesai) {
            return 'Sampai ' .
                Carbon::parse($selesai)->translatedFormat('d F Y');
        }

        return 'Seluruh periode';
    }

    private function normalizeDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $date = DateTime::createFromFormat('Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value ? $value : null;
    }

    private function csvRow($handle, array $columns): void
    {
        $columns = array_map(static function ($value) {
            if (
                is_string($value) &&
                preg_match('/^[\s]*[=+@-]|^[\t\r\n]/u', $value)
            ) {
                return "'" . $value;
            }
            return $value;
        }, $columns);
        fputcsv($handle, $columns, ';', '"', '');
    }
}
