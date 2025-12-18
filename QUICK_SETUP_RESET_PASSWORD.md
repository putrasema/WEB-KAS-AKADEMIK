# Quick Setup - Reset Password Feature

## ⚡ Setup Cepat (3 Langkah)

### Langkah 1: Jalankan SQL di phpMyAdmin

Buka **phpMyAdmin**, pilih database `academic_cash_db`, lalu jalankan SQL ini:

```sql
-- Add email column
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `email` VARCHAR(100) DEFAULT NULL AFTER `full_name`;

-- Add reset token columns  
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `reset_token` VARCHAR(100) DEFAULT NULL AFTER `email`,
ADD COLUMN IF NOT EXISTS `reset_token_expires` DATETIME DEFAULT NULL AFTER `reset_token`;

-- Update admin email
UPDATE `users` SET `email` = 'keisyaaurora325@gmail.com' WHERE `username` = 'admin';

-- Verify
SELECT username, email FROM users WHERE username = 'admin';
```

### Langkah 2: Test Forgot Password

1. Buka browser: `http://localhost/academic_cash_system/login.php`
2. Klik link **"Lupa Password?"** (di bawah input password)
3. Masukkan username: **admin**
4. Klik **"Kirim Link Reset"**

### Langkah 3: Cek Email

- Buka email: **keisyaaurora325@gmail.com**
- Cari email dengan subject: **"Reset Password - Sistem Kas Akademik"**
- Jika tidak ada di Inbox, cek **Spam/Junk**

---

## 🔍 Jika Email Tidak Terkirim

Cek pesan error yang muncul di halaman forgot_password.php:

### Error: "Akun ini belum memiliki email terdaftar"
**Solusi:** Jalankan SQL update email di Langkah 1

### Error: "Gagal mengirim email. SMTP Error..."
**Solusi:** 
- Cek `config/email_config.php` sudah benar
- Pastikan App Password sudah benar
- Lihat `SETUP_EMAIL.md` untuk panduan

### Email tidak masuk
**Solusi:**
- Tunggu 1-2 menit
- Cek folder Spam/Junk
- Coba request ulang

---

## ✅ Verifikasi Database

Jalankan SQL ini untuk cek apakah setup sudah benar:

```sql
-- Cek struktur tabel
DESCRIBE users;

-- Cek email admin
SELECT id, username, full_name, email FROM users WHERE role = 'admin';
```

Pastikan ada kolom: `email`, `reset_token`, `reset_token_expires`

---

## 🎯 Test Flow Lengkap

1. ✅ Jalankan SQL di phpMyAdmin (Langkah 1)
2. ✅ Buka `login.php` → Klik "Lupa Password?"
3. ✅ Input username `admin` → Klik "Kirim Link Reset"
4. ✅ Lihat pesan sukses/error
5. ✅ Cek email inbox
6. ✅ Klik link di email
7. ✅ Set password baru
8. ✅ Login dengan password baru

---

## 📸 Screenshot

Jika ada error, screenshot halaman forgot_password.php setelah submit form, sehingga bisa dilihat pesan error lengkapnya.
