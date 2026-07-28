# 🛒 ARKA POS — Aplikasi Point of Sale

> **Aplikasi POS berbasis web modern** untuk bisnis retail, kuliner, dan UMKM.  
> Dibangun dengan **Laravel 12**, **Alpine.js**, dan **Tailwind CSS**.

---

## ✨ Fitur Utama

| Kategori | Fitur |
|---|---|
| 🏪 **Kasir / POS** | Scan barcode (kamera + manual), pencarian real-time, pending order, cetak struk thermal 80mm |
| 💳 **Pembayaran** | Tunai, QRIS via Midtrans |
| 📦 **Stok** | Multi-satuan (unit), stok per cabang, stok opname, transfer stok, stok masuk & purchase order |
| 🏬 **Multi-Cabang** | Kelola beberapa toko/cabang dari satu akun Super Admin |
| 👥 **Member & Loyalty** | Tier Bronze/Silver/Gold, poin loyalitas, voucher otomatis |
| 🎯 **Promo & Diskon** | Diskon %, nominal, BOGO, bundle, voucher code, promo by waktu/hari |
| 📊 **Laporan** | Dashboard grafik, laporan PNL, laporan produk, EOD, shift, export Excel |
| ⚙️ **Manajemen** | Multi-user (Super Admin / Admin Cabang / Kasir), pengaturan toko, supplier |
| 📱 **PWA** | Bisa di-install di HP seperti aplikasi native |
| 🖨️ **Cetak Faktur** | Faktur dot-matrix continuous form (sales, pembelian, stok masuk) ukuran 9.5in |
| 💾 **Backup Database** | Backup manual/terjadwal, download, restore, & sinkronisasi cloud |

---

## 🖥️ Persyaratan Sistem

| Komponen | Versi Minimum |
|---|---|
| PHP | 8.2 atau lebih baru |
| Composer | 2.x |
| Node.js + NPM | 18+ |
| Database | SQLite (sudah bawaan) **atau** MySQL 5.7+ |
| Web Server | Apache / Nginx / Laravel Artisan Serve |

**Ekstensi PHP yang dibutuhkan:**  
`BCMath`, `Ctype`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`, `PDO`, `PDO_SQLite` / `PDO_MySQL`, `Tokenizer`, `XML`, `GD`

---

## 🚀 Instalasi

### Langkah 1 — Extract & Masuk ke Folder

```bash
# Extract file ZIP, lalu masuk ke folder proyek
cd arka-pos
```

---

### Langkah 2 — Install PHP Dependencies

```bash
composer install
```

> ⏳ Proses ini membutuhkan koneksi internet dan mungkin memakan beberapa menit.

---

### Langkah 3 — Setup File Environment

```bash
# Windows
copy .env.example .env

# Linux / Mac
cp .env.example .env
```

Lalu generate APP_KEY:

```bash
php artisan key:generate
```

---

### Langkah 4 — Konfigurasi Database

Buka file `.env` dan sesuaikan pengaturan database:

#### 🔹 Opsi A: SQLite (Paling Mudah, Tanpa Setup Tambahan)

```env
DB_CONNECTION=sqlite
```

Buat file database SQLite-nya:

```bash
# Windows
type nul > database\database.sqlite

# Linux / Mac
touch database/database.sqlite
```

#### 🔹 Opsi B: MySQL

Buat database di MySQL terlebih dahulu:

```sql
CREATE DATABASE arka_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Lalu edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=arka_pos
DB_USERNAME=root
DB_PASSWORD=password_anda
```

---

### Langkah 5 — Jalankan Migrasi Database

```bash
php artisan migrate
```

---

### Langkah 6 — Isi Data Awal (Seed)

```bash
php artisan db:seed
```

> Ini akan membuat akun admin default, kategori, produk contoh, dan pengaturan toko.

---

### Langkah 7 — Install & Build Frontend

```bash
npm install
npm run build
```

---

### Langkah 8 — Storage Link

```bash
php artisan storage:link
```

> Perintah ini menghubungkan folder upload agar gambar produk bisa ditampilkan.

---

### Langkah 9 — Jalankan Aplikasi

```bash
php artisan serve
```

Buka browser dan akses: **http://localhost:8000**

---

## 🔑 Akun Login Default

| Role | Username / Email | Password |
|---|---|---|
| **Super Admin** | `26050001` | `password` |
| **Admin Cabang** | `26050002` | `password` |
| **Kasir** | `26050005` | `password` |

> ⚠️ Segera ganti password default setelah login pertama!

---

## 👤 Struktur Hak Akses

| Role | Akses |
|---|---|
| `super_admin` | Semua fitur di semua cabang |
| `admin` | Manajemen cabang sendiri (produk, stok, laporan, user kasir) |
| `kasir` | Terminal POS, riwayat transaksi, clock in/out |

---

## 📋 Panduan Mulai Cepat

1. **Login** sebagai Super Admin (`26050001` / `password`)
2. **Atur toko** → Sidebar: *Settings → Store Settings* (nama toko, alamat, pajak, dll)
3. **Tambah cabang** (opsional) → *Settings → Branch*
4. **Tambah produk** → *Stock → Products → Add Product*
5. **Buka POS** → Klik menu *Point of Sale* di sidebar
6. **Scan / cari produk** → pilih member (opsional) → klik **Process Checkout**
7. **Pilih metode pembayaran** → cetak struk

---

## 💳 Konfigurasi Pembayaran QRIS (Midtrans)

Untuk mengaktifkan pembayaran QRIS, tambahkan ke file `.env`:

```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
```

> Daftar akun Midtrans di [midtrans.com](https://midtrans.com) untuk mendapatkan Server Key.  
> Ganti `MIDTRANS_IS_PRODUCTION=true` saat siap ke production.

---

## 🌐 Deploy ke Hosting / VPS

### Apache (cPanel / Shared Hosting)

1. Upload seluruh isi folder ke `public_html/nama-folder/`
2. Arahkan **Document Root** domain ke folder `public/`
3. Pastikan `.htaccess` di folder `public/` aktif (mod_rewrite enabled)
4. Jalankan semua langkah instalasi via SSH atau terminal hosting

### Nginx

Tambahkan konfigurasi Nginx:

```nginx
server {
    listen 80;
    server_name domain-anda.com;
    root /var/www/arka-pos/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Pengaturan Production

Edit `.env` untuk production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com
```

Lalu optimalkan:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🛠️ Solusi Masalah Umum

**❓ Gambar produk tidak muncul**  
Jalankan: `php artisan storage:link`

**❓ Error 500 setelah deploy**  
Pastikan permission folder `storage/` dan `bootstrap/cache/` bisa ditulis (chmod 775 di Linux)

**❓ Auto-print struk tidak bekerja**  
Aktifkan di: *Settings → Store Settings → Auto Print*

**❓ Lupa password admin**  
```bash
php artisan tinker
# Lalu jalankan:
User::where('username', '26050001')->first()->update(['password' => bcrypt('password_baru')]);
```

**❓ Error database foreign key (SQLite)**  
Jalankan: `php artisan db:seed --force`

**❓ Halaman blank / CSS tidak muncul**  
Jalankan: `npm install && npm run build`

---

## 📁 Struktur Proyek

```
arka-pos/
├── app/
│   ├── Console/Commands/    # Artisan commands (backup scheduler, dll)
│   ├── Http/Controllers/    # 30+ logic controller
│   ├── Jobs/                # Queue jobs (sync backup cloud)
│   ├── Models/              # 34 Eloquent models
│   └── Services/            # Service classes (promotion engine)
├── database/
│   ├── migrations/          # Migration files
│   └── seeders/             # Data awal aplikasi
├── public/                  # Root web server (arahkan domain ke sini)
│   └── build/               # Asset frontend hasil build
├── resources/
│   └── views/               # Template Blade (UI)
│       ├── pages/           # Halaman aplikasi
│       └── print/           # Template cetak (struk, faktur, opname)
├── routes/                  # Definisi URL/route
├── storage/                 # Upload file & cache
├── .env.example             # Template konfigurasi
└── composer.json            # PHP dependencies
```

---

## 📞 Support & Bantuan

Jika mengalami kesulitan dalam instalasi atau penggunaan, silakan hubungi:

| Kontak | Info |
|---|---|
| 📱 WhatsApp | [08971389076](https://wa.me/6208971389076) |
| 📧 Email | [arkastudio462@gmail.com](mailto:arkastudio462@gmail.com) |

> Response time: **1×24 jam** pada hari kerja.

---

## ⚖️ Lisensi & Ketentuan Penggunaan

Source code ini dijual sebagai **produk komersial**.

✅ **Diizinkan:**
- Menggunakan untuk 1 (satu) proyek / bisnis
- Modifikasi sesuai kebutuhan bisnis Anda
- Deploy ke server Anda sendiri

❌ **Dilarang:**
- Mendistribusikan ulang source code kepada pihak lain
- Menjual kembali source code (resell) tanpa izin tertulis
- Menghapus credit / watermark dari aplikasi
- Mengklaim sebagai karya sendiri

---

*© 2026 ARKA Studio. All rights reserved.*
