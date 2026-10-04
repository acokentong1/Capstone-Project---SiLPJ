# SILPJ

SILPJ adalah aplikasi Laravel untuk pencatatan belanja dan penyusunan dokumen pendukung LPJ seperti Nota Pesanan, Kwitansi, dan BAPB.

## Requirement

- PHP 8.2 atau lebih baru
- Composer
- Node.js dan npm
- SQLite/MySQL sesuai konfigurasi `.env`

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve
```

Jangan commit file `.env`, folder `vendor`, atau `node_modules` ke repository.

## Perubahan keamanan versi revisi

- Semua pembuatan dokumen memverifikasi bahwa transaksi milik user yang login.
- Nilai Kwitansi dan nomor dokumen penting ditentukan oleh server, bukan dipercaya dari hidden input browser.
- Database membatasi satu transaksi menjadi maksimal satu Nota Pesanan, satu Kwitansi, dan satu BAPB.
- Satu user hanya dapat memiliki satu profil sekolah.
- Validasi kategori belanja dibatasi pada `Barang` atau `Jasa`.
- Total belanja dihitung ulang oleh server.
- Route resource yang belum diimplementasikan tidak lagi diekspos.
- Timezone default aplikasi menggunakan `Asia/Makassar` dan locale default `id` melalui `.env.example`.
- Feature test keamanan dasar tersedia di `tests/Feature/Silpj/DataSecurityTest.php`.

## Upgrade database yang sudah berisi data

Migration `2026_10_04_220000_harden_lpj_data_integrity.php` akan menambahkan unique constraint. Jika database lama memiliki data duplikat, migration sengaja berhenti dan menampilkan pesan agar data diperiksa terlebih dahulu. Migration ini tidak menghapus data pengguna secara otomatis.

Setelah mengganti source code, jalankan:

```bash
php artisan optimize:clear
php artisan migrate
```

Untuk pemeriksaan otomatis:

```bash
php artisan test
```
