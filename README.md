# Academic Cash System (Sistem Kas Akademik)

Sistem Kas Akademik adalah aplikasi berbasis web untuk mengelola keuangan (kas/SPP) mahasiswa secara otomatis dengan integrasi kurs mata uang dan notifikasi email.

## 🚀 Fitur Utama
- **Manajemen Transaksi**: Pendataan pemasukan (income) dan pengeluaran (expense) dengan berbagai metode pembayaran.
- **Kurs Otomatis**: Integrasi dengan API kurs mata uang (Frankfurter) untuk konversi mata uang secara realtime.
- **Notifikasi Email**: Pengiriman pengingat pembayaran ke mahasiswa secara otomatis menggunakan PHPMailer & Gmail SMTP.
- **Analitik**: Grafik tren keuangan dan ekspor data ke Excel/PDF.
- **Multi-Currency**: Mendukung transaksi dalam IDR, USD, dan EUR.

## 🛠 Teknologi
- **Bahasa**: PHP 8.x (Native)
- **Database**: MySQL / MariaDB
- **Frontend**: Bootstrap 5, Chart.js, Bi-Icons
- **Dependencies**: PHPMailer, phpdotenv (Manajemen Environment)

## 📁 Struktur Folder Dasar
- **`public/`**: Folder publik (Index, Login, Dashboard, Assets). Web server sebaiknya mengarah ke folder ini.
- **`src/`**: Source code utama aplikasi (Config, Services, includes, Database). Tidak dapat diakses langsung oleh browser.
- **`.env`**: Konfigurasi sensitif (Database, SMTP, API Key).

## ⚙ Instalasi & Setup Lokal

1. **Siapkan Database**:
   - Buat database baru di MySQL (contoh: `academic_cash_db`).
   - Import file `src/Database/schema.sql`.

2. **Konfigurasi Environment**:
   - Salin file `.env.example` menjadi `.env`.
   - Sesuaikan konfigurasi database (`DB_USER`, `DB_PASS`, dll) dan SMTP email.

3. **Vendor Autoload**:
   - Pastikan folder `vendor/` sudah ada (Jalankan `composer install` jika perlu).

4. **Akses Aplikasi**:
   - Jika menggunakan XAMPP: `http://localhost/academic_cash_system/`
   - Direktori root otomatis akan mengalihkan (redirect) Anda ke folder `public/`.

## 🛡️ Keamanan (PENTING)
Jangan pernah meng-upload file `.env` ke repositori publik (GitHub). File ini sudah masuk dalam `.gitignore`.

---
*Dibuat untuk mempermudah pengelolaan kas akademik secara transparan dan efisien.*
