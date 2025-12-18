# Troubleshooting Email Reset Password

## Langkah-langkah Debug

### 1. **Pastikan Database Migration Sudah Dijalankan**

Buka phpMyAdmin atau MySQL client, jalankan query ini untuk cek apakah kolom sudah ada:

```sql
DESCRIBE users;
```

Pastikan ada kolom:
- `email` VARCHAR(100)
- `reset_token` VARCHAR(100)
- `reset_token_expires` DATETIME

Jika belum ada, jalankan migration:

```sql
-- File: database/migration_add_password_reset.sql
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `email` VARCHAR(100) DEFAULT NULL AFTER `full_name`,
ADD UNIQUE KEY IF NOT EXISTS `email` (`email`);

ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `reset_token` VARCHAR(100) DEFAULT NULL AFTER `email`,
ADD COLUMN IF NOT EXISTS `reset_token_expires` DATETIME DEFAULT NULL AFTER `reset_token`;

COMMIT;
```

### 2. **Update Email untuk User Admin**

```sql
-- Ganti dengan email Gmail Anda yang sudah dikonfigurasi
UPDATE `users` SET `email` = 'keisyaaurora325@gmail.com' WHERE `username` = 'admin';
```

Verifikasi email sudah tersimpan:

```sql
SELECT username, full_name, email FROM users WHERE username = 'admin';
```

### 3. **Test Email dengan Test Page**

Buka halaman test yang sudah saya buat:

```
http://localhost/academic_cash_system/test_reset_email.php
```

Halaman ini akan:
- ✅ Cek struktur database
- ✅ Cek email admin
- ✅ Generate token dan link reset
- ✅ Simpan token ke database
- ✅ Kirim email reset password
- ✅ Tampilkan hasil detail (sukses/gagal)

### 4. **Cek Konfigurasi Email**

File: `config/email_config.php`

Pastikan sudah diisi dengan benar:

```php
define('SMTP_USERNAME', 'keisyaaurora325@gmail.com');
define('SMTP_PASSWORD', 'nqhy ohxw frin ekhm'); // App Password dari Google
define('SMTP_FROM_EMAIL', 'keisyaaurora325@gmail.com');
```

### 5. **Test Forgot Password Flow**

Setelah database dan email sudah OK:

1. **Logout** dari admin (jika sedang login)
2. Buka `http://localhost/academic_cash_system/login.php`
3. Klik link **"Lupa Password?"**
4. Masukkan username: `admin`
5. Klik **"Kirim Link Reset"**
6. Cek pesan yang muncul:
   - ✅ Hijau = Email berhasil dikirim
   - ❌ Merah = Ada error (baca pesan error untuk detail)

### 6. **Cek Email Inbox**

- Buka email: `keisyaaurora325@gmail.com`
- Cari email dengan subject: **"Reset Password - Sistem Kas Akademik"**
- Jika tidak ada di Inbox, cek folder **Spam/Junk**
- Klik link **"Reset Password Saya"** di email

### 7. **Common Issues & Solutions**

#### Issue: "Akun ini belum memiliki email terdaftar"
**Solusi:** Jalankan UPDATE query di step 2

#### Issue: "Gagal mengirim email. SMTP Error..."
**Solusi:** 
- Cek `config/email_config.php` sudah benar
- Pastikan menggunakan App Password (bukan password biasa)
- Pastikan 2-Step Verification aktif di Google Account

#### Issue: Email tidak masuk
**Solusi:**
- Cek folder Spam/Junk
- Tunggu 1-2 menit (kadang delay)
- Cek log notifikasi di database:
  ```sql
  SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5;
  ```

#### Issue: "Link reset password tidak valid"
**Solusi:**
- Token mungkin sudah digunakan atau expired
- Request reset password ulang

## Quick Test Commands

```sql
-- Cek email admin
SELECT username, email FROM users WHERE username = 'admin';

-- Update email admin
UPDATE users SET email = 'your-email@gmail.com' WHERE username = 'admin';

-- Cek token yang sudah dibuat
SELECT username, email, reset_token, reset_token_expires FROM users WHERE reset_token IS NOT NULL;

-- Clear token (untuk test ulang)
UPDATE users SET reset_token = NULL, reset_token_expires = NULL WHERE username = 'admin';

-- Cek log email
SELECT * FROM notifications WHERE type = 'email' ORDER BY created_at DESC LIMIT 10;
```

## Test Flow Lengkap

1. ✅ Jalankan migration SQL
2. ✅ Update email admin
3. ✅ Buka `test_reset_email.php` untuk test kirim email
4. ✅ Jika sukses, test flow lengkap via `forgot_password.php`
5. ✅ Cek email inbox
6. ✅ Klik link reset di email
7. ✅ Set password baru
8. ✅ Login dengan password baru

## Bantuan Tambahan

Jika masih ada masalah, cek:
- File `SETUP_EMAIL.md` untuk setup email Gmail
- Log notifikasi di database table `notifications`
- PHP error log di server
