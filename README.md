# 🩺 DeLISA -- Deteksi Dini Ibu Nifas Berbasis Laravel 12

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-Bundler-646CFF?style=flat-square&logo=vite&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?style=flat-square&logo=php&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-38BDF8?style=flat-square&logo=tailwindcss&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)
![Status](https://img.shields.io/badge/Status-Stable-success?style=flat-square)

> **DeLISA** adalah sistem informasi berbasis web untuk deteksi dini dan
> pemantauan kesehatan ibu nifas, dikembangkan menggunakan **Laravel
> 12** dan **Vite** dengan arsitektur modular, aman, dan terintegrasi
> lintas fasilitas kesehatan.

------------------------------------------------------------------------

## 📘 Daftar Isi

-   [Deskripsi Proyek](#deskripsi-proyek)
-   [Struktur Folder](#struktur-folder)
-   [Fitur Utama](#fitur-utama)
-   [Kebutuhan Sistem](#kebutuhan-sistem)
-   [Instalasi dan Konfigurasi](#instalasi-dan-konfigurasi)
-   [Struktur Database](#struktur-database)
-   [Keamanan Aplikasi](#keamanan-aplikasi)
-   [Kontribusi](#kontribusi)
-   [Lisensi](#lisensi)
-   [Tim Pengembang](#tim-pengembang)

------------------------------------------------------------------------

## 🧭 Deskripsi Proyek

Aplikasi **DeLISA (Deteksi Dini Ibu Nifas)** digunakan untuk: - Memantau
kondisi ibu nifas secara digital dan real-time. - Melakukan skrining
risiko pre-eklampsia. - Mengelola data pasien, bidan, puskesmas, dan
rumah sakit secara terintegrasi. - Menyediakan dasbor analitik bagi
Dinas Kesehatan untuk mengambil keputusan berbasis data.

Sistem ini dikembangkan sebagai bagian dari proyek **Lagi Lagi PBL**
menggunakan **Laravel 12 + Vite**, dengan **PostgreSQL** sebagai
basis data.

------------------------------------------------------------------------

## 🗂 Struktur Folder

``` bash
delisa/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Dinkes/
│   │   │   ├── Puskesmas/
│   │   │   ├── Bidan/
│   │   │   ├── Rs/
│   │   │   └── Pasien/
│   ├── Models/
│   └── Middleware/
│
├── bootstrap/
│   └── app.php
│
├── config/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── database-delisa.sql
│
├── public/
│   ├── images/
│   ├── icons/
│   └── index.php
│
├── resources/
│   ├── css/
│   ├── js/
│   │   ├── dinkes/
│   │   ├── puskesmas/
│   │   ├── pasien/
│   │   ├── components/
│   │   └── app.js
│   └── views/
│       ├── dinkes/
│       ├── pasien/
│       ├── rs/
│       ├── puskesmas/
│       └── layouts/
│
├── routes/
│   ├── web.php
│   └── api.php
│
├── storage/
├── tests/
├── vite.config.js
└── README.md
```

📘 **Catatan:**\
Struktur di atas mengikuti konvensi Laravel 12. Tidak ada `kernel.php`,
dan konfigurasi aplikasi berada di `bootstrap/app.php`.

------------------------------------------------------------------------

## ⚙️ Fitur Utama

  -----------------------------------------------------------------------
  Modul                        Deskripsi
  ---------------------------- ------------------------------------------
  🧑‍⚕️ **Autentikasi             Role terpisah untuk DINKES, Puskesmas,
  Multi-Role**                 Bidan, RS, dan Pasien.

  🧾 **Data Master**           Kelola akun dan fasilitas kesehatan
                               (approve/reject akun baru).

  💉 **Skrining                Analisis risiko ibu nifas melalui
  Pre-Eklampsia**              kuisioner dan pemeriksaan.

  👩‍🍼 **Pemantauan Nifas**      Catat kunjungan, hasil pemeriksaan, dan
                               rujukan pasien.

  📊 **Analitik Kesehatan**    Visualisasi data berbentuk donut dan
                               grafik tren bulanan.

  🧱 **Keamanan Lengkap**      Menggunakan CSP, sanitasi input, CSRF
                               token, dan prepared statement.

  💡 **Vite Asset Loader**     Semua JS & CSS diatur via Vite (tanpa
                               inline script).
  -----------------------------------------------------------------------

------------------------------------------------------------------------

## 🖥️ Kebutuhan Sistem

  Komponen   Versi Minimum
  ---------- -------------------------------
  PHP        8.2
  Laravel    12.x
  Node.js    20.x
  Composer   2.x
  Database   PostgreSQL atau MySQL 8
  Vite       Default Laravel Asset Bundler
  Browser    Chrome / Firefox terbaru

------------------------------------------------------------------------

## ⚡ Instalasi dan Konfigurasi

### 1️⃣ Clone repositori

``` bash
git clone https://github.com/yourusername/delisa.git
cd delisa
```

### 2️⃣ Instal dependency PHP

``` bash
composer install
```

### 3️⃣ Instal dependency frontend

``` bash
npm install
```

### 4️⃣ Salin file environment

``` bash
cp .env.example .env
```

### 5️⃣ Konfigurasi database

Buka file `.env` lalu ubah bagian berikut:

``` bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=delisa
DB_USERNAME=root
DB_PASSWORD=
```

### 6️⃣ Generate key aplikasi

``` bash
php artisan key:generate
```

### 7️⃣ Migrasi dan seed database

``` bash
php artisan migrate --seed
```

### 8️⃣ Jalankan server backend

``` bash
php artisan serve
```

### 9️⃣ Jalankan Vite (frontend build & HMR)

``` bash
npm run dev
```

------------------------------------------------------------------------

## 🧩 Struktur Database

Sistem ini terdiri dari **30+
tabel utama** dengan foreign key terintegrasi.\
Beberapa entitas penting:

  -----------------------------------------------------------------------
  Tabel                        Deskripsi
  ---------------------------- ------------------------------------------
  `users`                      Akun pengguna seluruh role (status
                               aktif/nonaktif).

  `roles`                      Jenis peran: Dinkes, Bidan, Puskesmas, RS,
                               Pasien.

  `pasiens`                    Data pribadi, alamat, dan rekam kehamilan.

  `skrinings`                  Hasil skrining pre-eklampsia.

  `kf`                         Catatan kunjungan nifas ibu dan anak.

  `puskesmas`, `rumah_sakits`, Data fasilitas kesehatan.
  `bidans`                     

  `rujukan_rs`,                Data rujukan pasien antar fasilitas.
  `rujukan_nifas`              

  `riwayat_kehamilans`,        Riwayat medis lengkap pasien.
  `kondisi_kesehatans`         
  -----------------------------------------------------------------------

> Semua relasi antar tabel menggunakan `ON DELETE CASCADE` untuk menjaga
> integritas data.

------------------------------------------------------------------------

## 🔐 Keamanan Aplikasi

DeLISA menerapkan praktik keamanan modern: - **Content Security Policy
(CSP)** ketat dengan whitelist domain. - Tidak menggunakan inline script
--- seluruh JS di-load via Vite. - **CSRF Protection** otomatis di semua
form. - **Prepared Statements** untuk semua query SQL (mencegah SQL
Injection). - **Header Security Middleware** yang mengatur izin
`connect-src`, `font-src`, `img-src`, dan `style-src`.

------------------------------------------------------------------------

## 🤝 Kontribusi

1.  Fork repositori ini

2.  Buat branch fitur:

    ``` bash
    git checkout -b fitur-baru-anda
    ```

3.  Commit perubahan:

    ``` bash
    git commit -m "Menambahkan fitur baru"
    ```

4.  Push branch Anda dan ajukan Pull Request

> Pastikan kode mengikuti standar **PSR-12** dan tidak ada inline script
> di view Blade.

------------------------------------------------------------------------

## 🧾 Lisensi

Proyek ini menggunakan lisensi **MIT**.\
Anda bebas memodifikasi, menggunakan, dan menyebarluaskan untuk tujuan
akademik atau pengembangan lebih lanjut.

------------------------------------------------------------------------

## 👨‍💻 Tim Pengembang

**Kelompok Lagi Lagi PBL**

📍 Teknologi: Laravel 12 • Vite • TailwindCSS • PostgreSQL\
📅 Tahun: 2025

------------------------------------------------------------------------

> ✨ *"Membangun program dengan baik berarti membangun dokumentasi yang
> baik."*\
> -- Tim Delisa, 2025
