# Academic Cash System (Sistem Kas Akademik)

Sistem Kas Akademik adalah aplikasi berbasis web yang dirancang untuk mengelola keuangan (kas/SPP) mahasiswa secara efisien, transparan, dan otomatis. Aplikasi ini menggabungkan manajemen transaksi dengan fitur analitik keuangan dan integrasi notifikasi real-time.

## 🚀 Fitur Utama

### 1. Manajemen Keuangan & Transaksi
- **Pencatatan Pemasukan & Pengeluaran**: Sistem entry data yang mudah untuk mencatat setiap transaksi kas atau SPP.
- **Kategori Transaksi**: Manajemen kategori yang dinamis (e.g., Uang Kas, Event, Pembelian Perlengkapan) dengan indikator visual.
- **Multi-Currency Support**: Mendukung transaksi dalam mata uang **IDR (Rupiah)**, **USD (Dolar AS)**, dan **EUR (Euro)**.

### 2. Dashboard & Analitik
- **Real-time Summary**: Ringkasan saldo, total pemasukan, dan pengeluaran secara langsung di dashboard.
- **Grafik Tren**: Visualisasi data keuangan menggunakan **Chart.js** untuk melihat tren pemasukan dan pengeluaran per bulan.
- **Konversi Mata Uang**: Integrasi dengan API eksternal (Frankfurter) untuk menampilkan estimasi nilai saldo dalam mata uang asing secara real-time.

### 3. Manajemen Pengguna & Keamanan
- **Role-based Access Control (RBAC)**: Pemisahan hak akses antara **Administrator** (akses penuh) dan **Mahasiswa** (view-only/personal).
- **Registrasi & Otentikasi**: Sistem login dan registrasi aman dengan enkripsi password (Bcrypt).
- **Fitur Lupa Password**: Reset password aman melalui email token.

### 4. Notifikasi
- **Pengingat Otomatis**: Pengiriman email pengingat pembayaran kas/SPP kepada mahasiswa yang belum melunasi.
- **Integrasi SMTP**: Menggunakan **PHPMailer** dan **Gmail SMTP** untuk pengiriman email yang andal.

---

## 💻 Detail Teknologi (Tech Stack)

Aplikasi ini dibangun menggunakan pendekatan **Native PHP** dengan struktur kode yang modular dan berorientasi objek (OOP), tanpa menggunakan framework backend berat, untuk memastikan performa ringan dan kemudahan deployment.

### 🎨 Frontend (Antarmuka Pengguna)
- **HTML5 & CSS3**: Struktur semantik dan styling modern.
- **Bootstrap 5.3**: Framework CSS untuk desain yang responsif (mobile-friendly), sistem grid, dan komponen UI (Modal, Card, Alert).
- **JavaScript (Vanilla & ES6)**: Logika interaksi sisi klien.
- **Chart.js**: Library visualisasi data untuk merender grafik keuangan yang interaktif.
- **Bootstrap Icons**: Ikon vektor untuk mempercantik antarmuka.
- **Google Fonts**: Menggunakan font 'Outfit' dan 'Inter' untuk tipografi yang modern.

### ⚙️ Backend (Logika & Server)
- **PHP 8.x**: Bahasa pemrograman server-side utama.
- **MySQL / MariaDB**: Sistem manajemen database relasional (RDBMS) untuk menyimpan data pengguna, transaksi, dan log.
- **MVC Pattern (Simplified)**: Mengadopsi pola arsitektur Model-View-Controller dimana:
    - **Models/Services**: Menangani logika bisnis dan akses database (`src/Services/`).
    - **Controllers/Actions**: Menangani permintaan HTTP dan input form (`public/*_action.php`).
    - **Views**: Menangani tampilan output (`public/*.php`).
- **Composer**: Manajer dependensi untuk mengelola library pihak ketiga.

### 📚 Library & Integrasi Pihak Ketiga
- **PHPMailer**: Untuk pengiriman email transaksional yang aman dan andal.
- **vlucas/phpdotenv**: Untuk mengelola variabel lingkungan (environment variables) sensitif secara aman.
- **Frankfurter API**: API publik untuk mendapatkan data kurs mata uang asing secara real-time.

---

## 📁 Struktur Direktori

```bash
academic_cash_system/
├── .env                # Konfigurasi environment (DB, SMTP)
├── public/             # Direktori akses publik (Web Root)
│   ├── assets/         # CSS, JS, Images
│   ├── *.php           # File-file View & Controller (Login, Dashboard, dll)
│   └── setup_database.php # Script instalasi database otomatis
├── src/                # Core Application Logic
│   ├── Config/         # Konfigurasi Database & Init
│   ├── Services/       # Service Classes (Auth, Database, Notification)
│   └── Includes/       # Potongan kode reusable (Header, Sidebar)
├── vendor/             # Library dependensi (Composer)
└── README.md           # Dokumentasi Proyek
```

---

## 🛠 Instalasi & Cara Menjalankan

### Prasyarat
- Web Server (Apache/Nginx) atau XAMPP/Laragon.
- PHP versi 8.0 atau lebih baru.
- MySQL Server.
- Composer (Opsional, jika ingin update dependency).

### Langkah-Langkah

1. **Clone Repositori**
   ```bash
   git clone https://github.com/putrasema/WEB-KAS-AKADEMIK.git
   cd WEB-KAS-AKADEMIK
   ```

2. **Setup Ketergantungan**
   Jika folder `vendor/` belum ada, jalankan:
   ```bash
   composer install
   ```

3. **Konfigurasi Environment**
   - Salin file `.env.example` ke `.env`:
     ```bash
     cp .env.example .env
     ```
   - Buka file `.env` dan sesuaikan pengaturan Database dan SMTP Email Anda.

4. **Instalasi Database**
   - Jalankan script setup otomatis melalui browser atau terminal:
     ```bash
     php public/setup_database.php
     ```
   - ATAU import manual file `public/database/schema.sql` ke database MySQL Anda.

5. **Jalankan Aplikasi**
   - Jika menggunakan PHP Built-in Server:
     ```bash
     php -S localhost:8000
     ```
   - Buka browser dan akses: `http://localhost:8000`

---

## 👥 Kontributor
- **Fairu** - *Initial Work & Development*

---
*Dibuat dengan ❤️ untuk kemudahan administrasi akademik.*
