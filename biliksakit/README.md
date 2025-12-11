# Sistem Pengurusan Kesihatan Pelajar - Bilik Sakit Asrama KVSP1

Sistem web untuk mengurus penggunaan bilik sakit di sekolah menggunakan PHP Native, Bootstrap, dan Font Awesome.

## Ciri-ciri

- **Modul Biro**: Memasukkan maklumat pelajar yang sakit
- **Modul Petugas**: Menyetujui atau menolak permohonan masuk bilik sakit
- **Sistem Status**: Menunggu, Diluluskan, Ditolak
- **Pengurusan Bilik**: Auto-assign bilik isolasi apabila permohonan diluluskan
- **UI Responsif**: Menggunakan Bootstrap 5
- **Ikon**: Menggunakan Font Awesome 6

## Keperluan

- PHP 7.4 atau lebih tinggi
- MySQL/MariaDB
- Web Server (Apache/Nginx) atau Laragon

## Pemasangan

1. Import database:
   ```sql
   mysql -u root -p < database.sql
   ```
   Atau import `database.sql` melalui phpMyAdmin

2. Konfigurasi database di `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'biliksakit');
   ```

3. Akses sistem melalui browser:
   ```
   http://localhost/biliksakit/login.php
   ```

## Akaun Demo

**Biro:**
- Username: `biro1`
- Password: `password`

**Petugas:**
- Username: `petugas1`
- Password: `password`

## Struktur Fail

- `config.php` - Konfigurasi database dan fungsi helper
- `login.php` - Halaman log masuk
- `logout.php` - Log keluar
- `dashboard.php` - Dashboard utama
- `permohonan_baru.php` - Biro: Buat permohonan baru
- `senarai_permohonan.php` - Biro: Lihat semua permohonan
- `pelajar.php` - Biro: Urus pelajar
- `kelulusan_permohonan.php` - Petugas: Setuju/tolak permohonan
- `urus_bilik.php` - Petugas: Lihat status bilik
- `database.sql` - Struktur database

## Alur Kerja

1. **Biro** log masuk → Input data pelajar sakit
2. Data dihantar ke **Petugas** (status: Menunggu)
3. **Petugas** log masuk → Lihat permohonan
4. **Petugas** pilih Setuju/Tolak
5. Sistem update status dan assign bilik isolasi (jika setuju)

## Teknologi

- PHP Native
- MySQL
- Bootstrap 5
- Font Awesome 6
- JavaScript

## Nota

- Semua password default adalah `admin123` (hashed dengan bcrypt)
- Pastikan session PHP berfungsi dengan baik
- Sistem menggunakan UTF-8 untuk sokongan Bahasa Malaysia


