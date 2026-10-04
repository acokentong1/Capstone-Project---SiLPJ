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
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanLpjController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->resolveFilters($request);

        $school = SchoolProfile::where('user_id', auth()->id())->first();

        $belanjas = $this->filteredQuery($filters)
            ->with(['notaPesanan', 'kwitansi', 'bapb'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $stats = $this->buildStats($belanjas);
        $periodeLabel = $this->periodeLabel($filters);
        $hasFilters = collect($filters)->contains(fn ($value) => filled($value));

        return view('laporan-lpj.index', compact(
            'school',
            'belanjas',
            'stats',
            'filters',
            'periodeLabel',
            'hasFilters'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $this->resolveFilters($request);
        $school = SchoolProfile::where('user_id', auth()->id())->first();

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
            ]
        );

        return response()->streamDownload(function () use ($school, $belanjas, $stats, $periodeLabel) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM agar karakter Indonesia terbaca baik saat dibuka di Excel.
            fwrite($handle, "\xEF\xBB\xBF");

            $this->csvRow($handle, ['LAPORAN PERTANGGUNGJAWABAN (LPJ)']);
            $this->csvRow($handle, ['Sekolah', $school?->nama_sekolah ?? '-']);
            $this->csvRow($handle, ['NPSN', $school?->npsn ?? '-']);
            $this->csvRow($handle, ['Periode', $periodeLabel]);
            $this->csvRow($handle, []);
            $this->csvRow($handle, ['RINGKASAN']);
            $this->csvRow($handle, ['Jumlah Transaksi', $stats['jumlahTransaksi']]);
            $this->csvRow($handle, ['Total Belanja', $stats['totalBelanja']]);
            $this->csvRow($handle, ['LPJ Lengkap', $stats['jumlahLengkap']]);
            $this->csvRow($handle, ['LPJ Dalam Proses', $stats['jumlahProses']]);
            $this->csvRow($handle, ['Belum Ada Dokumen', $stats['jumlahBelumAdaDokumen']]);
            $this->csvRow($handle, ['Kelengkapan Dokumen', $stats['persenKelengkapanDokumen'] . '%']);
            $this->csvRow($handle, ['Belanja Barang', $stats['totalBarang']]);
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
                $status = $jumlahDokumen === 3
                    ? 'Lengkap'
                    : ($jumlahDokumen === 0 ? 'Belum ada dokumen' : "Proses {$jumlahDokumen}/3");

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
            $this->csvRow($handle, ['TOTAL KESELURUHAN', '', '', '', '', '', '', $stats['totalBelanja']]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filteredQuery(array $filters): Builder
    {
        $query = Belanja::query()
            ->where('user_id', auth()->id());

        if ($filters['q'] !== '') {
            $search = $filters['q'];
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('nomor_bukti', 'like', "%{$search}%")
                    ->orWhere('uraian', 'like', "%{$search}%");
            });
        }

        if ($filters['kategori'] !== '') {
            $query->where('kategori', $filters['kategori']);
        }

        if ($filters['tanggal_mulai']) {
            $query->whereDate('tanggal', '>=', $filters['tanggal_mulai']);
        }

        if ($filters['tanggal_selesai']) {
            $query->whereDate('tanggal', '<=', $filters['tanggal_selesai']);
        }

        if ($filters['status'] === 'lengkap') {
            $query->whereHas('notaPesanan')
                ->whereHas('kwitansi')
                ->whereHas('bapb');
        } elseif ($filters['status'] === 'proses') {
            $query->where(function (Builder $builder) {
                $builder->whereHas('notaPesanan')
                    ->orWhereHas('kwitansi')
                    ->orWhereHas('bapb');
            })->where(function (Builder $builder) {
                $builder->whereDoesntHave('notaPesanan')
                    ->orWhereDoesntHave('kwitansi')
                    ->orWhereDoesntHave('bapb');
            });
        } elseif ($filters['status'] === 'belum') {
            $query->whereDoesntHave('notaPesanan')
                ->whereDoesntHave('kwitansi')
                ->whereDoesntHave('bapb');
        }

        if ($filters['dokumen_kurang'] === 'nota') {
            $query->whereDoesntHave('notaPesanan');
        } elseif ($filters['dokumen_kurang'] === 'kwitansi') {
            $query->whereDoesntHave('kwitansi');
        } elseif ($filters['dokumen_kurang'] === 'bapb') {
            $query->whereDoesntHave('bapb');
        }

        return $query;
    }

    private function resolveFilters(Request $request): array
    {
        $q = trim((string) $request->query('q', ''));
        $kategori = in_array($request->query('kategori'), ['Barang', 'Jasa'], true)
            ? (string) $request->query('kategori')
            : '';
        $status = in_array($request->query('status'), ['lengkap', 'proses', 'belum'], true)
            ? (string) $request->query('status')
            : '';
        $dokumenKurang = in_array($request->query('dokumen_kurang'), ['nota', 'kwitansi', 'bapb'], true)
            ? (string) $request->query('dokumen_kurang')
            : '';
        $tanggalMulai = $this->normalizeDate($request->query('tanggal_mulai'));
        $tanggalSelesai = $this->normalizeDate($request->query('tanggal_selesai'));

        if ($tanggalMulai && $tanggalSelesai && $tanggalMulai > $tanggalSelesai) {
            [$tanggalMulai, $tanggalSelesai] = [$tanggalSelesai, $tanggalMulai];
        }

        return [
            'q' => $q,
            'kategori' => $kategori,
            'status' => $status,
            'dokumen_kurang' => $dokumenKurang,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
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
            'persenLpjLengkap' => $jumlahTransaksi > 0
                ? (int) round(($jumlahLengkap / $jumlahTransaksi) * 100)
                : 0,
            'persenKelengkapanDokumen' => $maksimumDokumen > 0
                ? (int) round(($jumlahDokumenTersedia / $maksimumDokumen) * 100)
                : 0,
            'nilaiLpjLengkap' => $nilaiLpjLengkap,
            'jumlahBarang' => $belanjas->where('kategori', 'Barang')->count(),
            'jumlahJasa' => $belanjas->where('kategori', 'Jasa')->count(),
            'totalBarang' => (int) $belanjas->where('kategori', 'Barang')->sum('total'),
            'totalJasa' => (int) $belanjas->where('kategori', 'Jasa')->sum('total'),
        ];
    }

    private function documentCount(Belanja $belanja): int
    {
        return (int) (bool) $belanja->notaPesanan
            + (int) (bool) $belanja->kwitansi
            + (int) (bool) $belanja->bapb;
    }

    private function periodeLabel(array $filters): string
    {
        $mulai = $filters['tanggal_mulai'];
        $selesai = $filters['tanggal_selesai'];

        if ($mulai && $selesai) {
            if ($mulai === $selesai) {
                return Carbon::parse($mulai)->translatedFormat('d F Y');
            }

            return Carbon::parse($mulai)->translatedFormat('d F Y')
                . ' s.d. '
                . Carbon::parse($selesai)->translatedFormat('d F Y');
        }

        if ($mulai) {
            return 'Sejak ' . Carbon::parse($mulai)->translatedFormat('d F Y');
        }

        if ($selesai) {
            return 'Sampai ' . Carbon::parse($selesai)->translatedFormat('d F Y');
        }

        return 'Seluruh periode';
    }

    private function normalizeDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $date = DateTime::createFromFormat('Y-m-d', $value);

        return $date && $date->format('Y-m-d') === $value
            ? $value
            : null;
    }

    private function csvRow($handle, array $columns): void
    {
        fputcsv($handle, $columns, ';', '"', '');
    }
}
