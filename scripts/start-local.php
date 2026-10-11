<?php

// Peluncur lokal: pemeriksaan hanya membaca konfigurasi dan database.
$root = dirname(__DIR__);
chdir($root);

function stopLocal(string $message): never
{
    fwrite(STDERR, "\n[PERLU DIPERIKSA] {$message}\n");
    exit(1);
}

if (PHP_VERSION_ID < 80200) {
    stopLocal('Gunakan PHP 8.2 atau lebih baru sesuai dependensi proyek.');
}
if (!is_file($root.'/vendor/autoload.php')) {
    stopLocal('Folder vendor belum lengkap. Jalankan composer install di folder proyek.');
}
if (!is_file($root.'/.env')) {
    stopLocal('File .env belum tersedia. Gunakan konfigurasi instalasi Anda yang sudah berjalan.');
}
if (!function_exists('proc_open')) {
    stopLocal('proc_open tidak tersedia. Jalankan php artisan serve secara manual.');
}

try {
    require $root.'/vendor/autoload.php';
    $app = require $root.'/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
} catch (Throwable $error) {
    stopLocal('Laravel gagal dimuat. Jalankan php artisan about dari terminal untuk diagnosis.');
}

if (!config('app.key')) {
    stopLocal('APP_KEY kosong. Pulihkan kunci instalasi yang sama dari salinan konfigurasi Anda.');
}
try {
    $connection = Illuminate\Support\Facades\DB::connection();
    $connection->getPdo();
    $schema = $connection->getSchemaBuilder();
    $ready = $schema->hasTable('users')
        && $schema->hasTable('lpj_attachments')
        && $schema->hasTable('lpj_events')
        && $schema->hasColumn('belanjas', 'review_status');
} catch (Throwable $error) {
    stopLocal('Database belum dapat diakses. Nyalakan layanan database dan periksa konfigurasi .env.');
}
if (!$ready) {
    stopLocal('Struktur database belum lengkap. Periksa php artisan migrate:status dan panduan pemasangan.');
}
foreach (['storage', 'bootstrap/cache'] as $directory) {
    if (!is_writable($root.'/'.$directory)) {
        stopLocal("Folder {$directory} tidak dapat ditulis. Periksa izin folder proyek.");
    }
}
if (!is_file($root.'/public/build/manifest.json') && !is_file($root.'/public/hot')) {
    stopLocal('Aset tampilan belum tersedia. Jalankan npm ci lalu npm run build.');
}
if (is_file($root.'/public/hot')) {
    echo "[INFO] Mode Vite aktif. Pastikan npm run dev sedang berjalan.\n";
}

// Hanya loopback; pilih port kosong tanpa menghentikan aplikasi lain.
$port = null;
for ($candidate = 8000; $candidate <= 8010; $candidate++) {
    $socket = @stream_socket_server('tcp://127.0.0.1:'.$candidate, $errno, $error);
    if ($socket !== false) {
        fclose($socket);
        $port = $candidate;
        break;
    }
}
if ($port === null) {
    stopLocal('Port 8000-8010 sedang dipakai. Tutup server lokal yang sudah tidak digunakan.');
}
$url = 'http://127.0.0.1:'.$port;
echo "\nSiLPJ: {$url}\nBiarkan jendela ini terbuka. Tekan Ctrl+C setelah selesai.\n\n";
$process = proc_open(
    [PHP_BINARY, $root.'/artisan', 'serve', '--host=127.0.0.1', '--port='.$port, '--tries=1', '--no-reload'],
    [0 => STDIN, 1 => STDOUT, 2 => STDERR],
    $pipes,
    $root
);
if (!is_resource($process)) {
    stopLocal('Server gagal dijalankan. Coba php artisan serve dari terminal.');
}

// Tunggu server mendengarkan sebelum membuka browser.
$opened = false;
for ($attempt = 0; $attempt < 100; $attempt++) {
    $status = proc_get_status($process);
    if (!$status['running']) {
        proc_close($process);
        stopLocal('Server berhenti saat mulai. Periksa pesan di atas.');
    }
    $socket = @stream_socket_client('tcp://127.0.0.1:'.$port, $errno, $error, 0.1);
    if ($socket !== false) {
        fclose($socket);
        if (PHP_OS_FAMILY === 'Windows' && function_exists('popen')) {
            // URL dibentuk hanya dari alamat tetap dan port integer, bukan input pengguna.
            $browser = @popen('start "" "'.$url.'"', 'r');
            if (is_resource($browser)) {
                pclose($browser);
            }
        }
        $opened = true;
        break;
    }
    usleep(100000);
}
if (!$opened) {
    echo "Server masih memulai. Buka {$url} setelah pesan server siap tampil.\n";
}
exit(proc_close($process) === 0 ? 0 : 1);
