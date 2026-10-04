# Catatan Revisi SILPJ V2

## Perbaikan utama

1. Menutup celah akses dokumen antar-user pada Kwitansi, Nota Pesanan, BAPB, Belanja, dan Profil Sekolah.
2. Nomor Kwitansi, Nota Pesanan, dan BAPB tidak lagi dipercaya dari input browser saat penyimpanan.
3. Nilai Kwitansi diambil langsung dari total transaksi pada database.
4. Terbilang Kwitansi dibuat otomatis oleh server agar selalu sesuai dengan jumlah uang.
5. Menambahkan transaksi dan row locking untuk mengurangi risiko nomor dokumen ganda akibat request bersamaan.
6. Menambahkan unique constraint database untuk relasi satu transaksi satu dokumen.
7. Membatasi satu user menjadi satu profil sekolah dan menambahkan fungsi edit/update profil.
8. Menyamakan validasi Belanja saat create dan update, termasuk kategori `Barang`/`Jasa` dan harga integer.
9. Menghapus route resource yang belum diimplementasikan.
10. Dashboard tidak lagi bergantung pada verifikasi email yang belum diaktifkan pada model User.
11. Menambahkan default timezone `Asia/Makassar` serta locale Indonesia.
12. Menambahkan feature test keamanan dan unit test fungsi terbilang.

## Setelah mengganti source code

Pastikan `.env` lokal memiliki:

```env
APP_TIMEZONE=Asia/Makassar
APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID
```

Lalu jalankan:

```bash
composer install
npm install
php artisan optimize:clear
php artisan migrate
npm run build
php artisan test
```

Jika migration integritas data berhenti karena data duplikat, jangan menghapus data secara acak. Periksa duplikat yang disebutkan oleh pesan migration, tentukan dokumen yang benar, rapikan data, lalu jalankan `php artisan migrate` kembali.
