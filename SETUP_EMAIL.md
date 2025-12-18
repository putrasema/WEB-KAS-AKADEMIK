# 📧 Panduan Setup Gmail App Password

## Langkah 1: Aktifkan 2-Step Verification

1. Buka browser dan kunjungi: **https://myaccount.google.com/security**
2. Login dengan akun Gmail Anda
3. Scroll ke bawah, cari **"2-Step Verification"**
4. Klik **"2-Step Verification"**
5. Klik **"Get Started"** atau **"Turn On"**
6. Ikuti instruksi untuk setup (biasanya pakai nomor HP)
7. Selesaikan verifikasi

> ✅ Setelah selesai, status 2-Step Verification akan menjadi **"On"**

---

## Langkah 2: Generate App Password

1. Buka: **https://myaccount.google.com/apppasswords**
   - Atau dari halaman Security → cari **"App passwords"**
   
2. Anda akan diminta login lagi (untuk keamanan)

3. Di halaman App Passwords:
   - **Select app**: Pilih **"Mail"**
   - **Select device**: Pilih **"Other (Custom name)"**
   - Ketik nama: **"Academic Cash System"**
   - Klik **"Generate"**

4. Google akan menampilkan **16-digit password** dalam kotak kuning
   - Contoh: `abcd efgh ijkl mnop`
   - **SALIN password ini!** (klik tombol copy atau salin manual)

> ⚠️ Password ini hanya ditampilkan SEKALI! Jika hilang, harus generate ulang.

---

## Langkah 3: Update Konfigurasi Email

1. Buka file: **`config/email_config.php`**

2. Ganti 3 nilai berikut:

```php
// Email Gmail Anda
define('SMTP_USERNAME', 'admin@gmail.com'); // ← Ganti dengan email Anda

// App Password yang baru di-generate (16 digit)
define('SMTP_PASSWORD', 'abcd efgh ijkl mnop'); // ← Paste App Password di sini

// Email pengirim (sama dengan SMTP_USERNAME)
define('SMTP_FROM_EMAIL', 'admin@gmail.com'); // ← Ganti dengan email Anda
```

3. **Simpan file** (Ctrl+S)

---

## Langkah 4: Update Email Mahasiswa (Opsional)

Pastikan mahasiswa memiliki email di database:

### Via Web Interface:
1. Login sebagai admin
2. Buka menu **"Mahasiswa/i"**
3. Klik **"Edit"** pada mahasiswa
4. Isi kolom **"Email"**
5. Klik **"Simpan"**

### Via Database (Jika perlu):
```sql
-- Update email untuk mahasiswa tertentu
UPDATE students 
SET email = 'mahasiswa@example.com' 
WHERE student_id_number = '1227';
```

---

## Langkah 5: Test Pengiriman Email

1. Login sebagai **admin**
2. Buka halaman **"Notifikasi & Pengingat"**
3. Pilih mahasiswa yang sudah punya email
4. Klik tombol **"Kirim Email"** (tombol ungu)
5. Konfirmasi pengiriman
6. Tunggu beberapa detik
7. Cek inbox email mahasiswa

> ✅ Jika berhasil, akan muncul alert: **"Email berhasil dikirim"**

---

## Contoh Konfigurasi Lengkap

```php
<?php
// config/email_config.php

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_AUTH', true);

// Contoh dengan email admin@gmail.com
define('SMTP_USERNAME', 'admin@gmail.com');
define('SMTP_PASSWORD', 'abcd efgh ijkl mnop'); // App Password 16 digit
define('SMTP_FROM_EMAIL', 'admin@gmail.com');
define('SMTP_FROM_NAME', 'Sistem Kas Akademik');

define('EMAIL_CHARSET', 'UTF-8');
define('EMAIL_DEBUG', 0);
```

---

## Troubleshooting

### ❌ Error: "Could not authenticate"
- **Penyebab**: App Password salah atau belum di-setup
- **Solusi**: 
  - Pastikan sudah generate App Password (bukan password Gmail biasa)
  - Pastikan 2-Step Verification aktif
  - Copy-paste App Password dengan benar (16 karakter)

### ❌ Error: "Konfigurasi email belum diatur"
- **Penyebab**: File config masih pakai nilai placeholder
- **Solusi**: Update `SMTP_USERNAME`, `SMTP_PASSWORD`, dan `SMTP_FROM_EMAIL`

### ❌ Error: "Email mahasiswa tidak tersedia"
- **Penyebab**: Mahasiswa tidak punya email di database
- **Solusi**: Tambahkan email mahasiswa via menu Mahasiswa/i

---

## Checklist Setup ✓

- [ ] 2-Step Verification sudah aktif
- [ ] App Password sudah di-generate dan disalin
- [ ] File `config/email_config.php` sudah diupdate:
  - [ ] SMTP_USERNAME (email Gmail)
  - [ ] SMTP_PASSWORD (App Password 16 digit)
  - [ ] SMTP_FROM_EMAIL (email Gmail)
- [ ] File sudah disimpan
- [ ] Mahasiswa sudah punya email di database
- [ ] Test kirim email berhasil

---

## 🎉 Selesai!

Setelah semua langkah di atas selesai, sistem email sudah siap digunakan untuk mengirim notifikasi pembayaran otomatis!
