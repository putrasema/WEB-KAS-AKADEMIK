# 🔐 Panduan Akses Admin - Academic Cash System

## ✅ Database Berhasil Di-Setup!

Database `academic_cash_db` telah berhasil dibuat dengan data berikut:

### 📊 Statistik Database
- **Users**: 2 record(s)
- **Students**: 4 record(s)  
- **Transactions**: 2 record(s)
- **Categories**: 4 record(s)
- **Currencies**: 3 record(s)

---

## 🔑 Kredensial Login Admin

```
URL      : http://localhost:8000/login.php
Username : admin
Password : password
Role     : Administrator
```

---

## 🚀 Cara Menjalankan Sistem

### **Langkah 1: Jalankan Server PHP**

Buka CMD/Terminal dan jalankan:

```bash
cd C:\Users\fairu\.gemini\antigravity\scratch\academic_cash_system
php -S localhost:8000
```

### **Langkah 2: Buka Browser**

Akses salah satu URL berikut:
- **Homepage**: `http://localhost:8000`
- **Login Page**: `http://localhost:8000/login.php`
- **Admin Dashboard**: `http://localhost:8000/admin_dashboard.php` (setelah login)

### **Langkah 3: Login sebagai Admin**

1. Masukkan username: `admin`
2. Masukkan password: `password`
3. Klik tombol "Sign In"
4. Anda akan otomatis diarahkan ke **Dashboard Admin**

---

## 🎯 Fitur Dashboard Admin

Setelah login sebagai admin, Anda akan melihat:

### **Statistik Lengkap**
- ✅ Total Users dalam sistem
- ✅ Total Mahasiswa/i aktif
- ✅ Total Transaksi bulan ini
- ✅ Saldo Kas keseluruhan

### **Overview Keuangan**
- ✅ Pemasukan bulan ini
- ✅ Pengeluaran bulan ini
- ✅ Selisih (Surplus/Defisit)

### **Visualisasi**
- ✅ Grafik line chart 6 bulan terakhir
- ✅ Daftar transaksi terbaru

### **Aksi Cepat**
- ✅ Kelola Mahasiswa/i
- ✅ Lihat Transaksi
- ✅ Analitik
- ✅ Tambah User Baru

### **Menu Khusus Admin**
- ✅ Kelola User (di sidebar)
- ✅ Badge "Administrator" dengan icon shield
- ✅ Desain purple gradient theme

---

## 🔄 Setup Ulang Database

Jika ingin setup ulang database, jalankan:

```bash
php setup_database.php
```

Script akan:
1. Membuat database `academic_cash_db` (jika belum ada)
2. Membuat semua tabel yang diperlukan
3. Membuat/reset user admin
4. Menampilkan statistik database

---

## 👥 Membuat User Baru

### **Cara 1: Melalui Dashboard Admin**
1. Login sebagai admin
2. Klik menu "Kelola User" di sidebar
3. Atau akses: `http://localhost:8000/register.php`
4. Isi form registrasi
5. Pilih role: `admin`, `treasurer`, atau `student`

### **Cara 2: Melalui Database**
Jalankan query SQL:

```sql
INSERT INTO users (username, password, full_name, role) 
VALUES ('username_baru', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nama Lengkap', 'admin');
```

> **Note**: Password di atas adalah hash untuk 'password'

---

## 🔧 Troubleshooting

### **Problem: "Connection refused" atau "Cannot connect to MySQL"**

**Solusi:**
1. Pastikan MySQL/XAMPP sudah berjalan
2. Cek service MySQL di XAMPP Control Panel
3. Pastikan port 3306 tidak digunakan aplikasi lain

### **Problem: "Database not found"**

**Solusi:**
```bash
php setup_database.php
```

### **Problem: "Invalid username or password"**

**Solusi:**
1. Pastikan menggunakan username: `admin` dan password: `password`
2. Jika lupa password, jalankan:
```bash
php setup_database.php
```
Pilih 'y' untuk reset password

### **Problem: "Access denied for user 'root'"**

**Solusi:**
Edit file `classes/Database.php` dan sesuaikan kredensial:
```php
$host = 'localhost';
$db = 'academic_cash_db';
$user = 'root';  
$pass = '';      
```

---

## 📱 Akses dari Perangkat Lain

Jika ingin akses dari perangkat lain di jaringan yang sama:

```bash
# Jalankan server dengan IP 0.0.0.0
php -S 0.0.0.0:8000
```

Kemudian akses dari perangkat lain:
```
http://[IP_KOMPUTER_ANDA]:8000
```

Contoh: `http://192.168.1.100:8000`

---

## 🔐 Keamanan

### **Untuk Production:**

1. **Ganti Password Default**
   - Jangan gunakan password 'password' di production
   - Gunakan password yang kuat

2. **Update Database Credentials**
   - Jangan gunakan user 'root' tanpa password
   - Buat user MySQL khusus untuk aplikasi

3. **Enable HTTPS**
   - Gunakan SSL certificate
   - Redirect HTTP ke HTTPS

4. **Backup Database**
   ```bash
   mysqldump -u root academic_cash_db > backup.sql
   ```

---

## 📞 Bantuan

Jika mengalami masalah:
1. Cek log error di terminal/CMD
2. Pastikan semua service (MySQL, PHP) berjalan
3. Periksa file konfigurasi database
4. Jalankan ulang `setup_database.php`

---

## ✨ Selamat!

Database admin sudah siap digunakan! 🎉

Silakan login dan mulai menggunakan Academic Cash System.
