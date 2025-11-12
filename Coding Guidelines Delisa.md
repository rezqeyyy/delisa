
# 🧭 C O D I N G  G U I D E L I N E S  
### Proyek DeLISA (Laravel 12 + Vite)

---

## 1. Tujuan & Ruang Lingkup
Pedoman ini digunakan untuk:
- Menjaga **konsistensi** kode antar-pengembang.  
- Memastikan **keamanan**, **kinerja**, dan **kemudahan pemeliharaan**.  
- Menjadi referensi resmi selama pengembangan proyek **DeLISA** (Digitalisasi Layanan Ibu Nifas).  

Lingkup: seluruh kode sumber Laravel, Blade, JS Vite, CSS Tailwind, serta migrasi database SQL.

---

## 2. Arsitektur Aplikasi
- Framework: **Laravel 12**  
- Bundler: **Vite** (menggantikan Mix)  
- Entry Bootstrap: `bootstrap/app.php` *(tidak ada `Kernel.php`)*  
- Lapisan:
  | Layer | Deskripsi |
  |-------|------------|
  | **Model** | Representasi tabel database (Eloquent ORM) |
  | **Controller** | Logika bisnis ringan, memanggil service jika kompleks |
  | **Service/Action** | Proses spesifik terpisah agar controller bersih |
  | **View (Blade)** | Hanya presentasi; hindari logika kompleks |
  | **Component** | Blade komponen reusable untuk UI berulang |

---

## 3. Konvensi Penamaan

### 3.1 PHP / Laravel
| Elemen | Format | Contoh |
|---------|---------|---------|
| **Class / Model** | PascalCase | `Pasien`, `SkriningController` |
| **Method / Fungsi** | camelCase | `storeKf()`, `updateProfile()` |
| **Variabel** | camelCase | `$totalPasien`, `$isApproved` |
| **Konstanta / Enum** | UPPER_SNAKE | `ROLE_DINKES`, `STATUS_DIRUJUK` |
| **View file** | kebab-case | `dinkes-dashboard.blade.php` |
| **Route name** | dot.notation | `dinkes.dashboard`, `pasien.skrining` |

### 3.2 Database
| Elemen | Format | Contoh |
|---------|---------|---------|
| **Tabel** | plural snake_case | `pasiens`, `skrinings`, `kf` |
| **Kolom** | snake_case | `tanggal_kunjungan`, `status_pre_eklampsia` |
| **Foreign Key** | `<entitas>_id` | `puskesmas_id`, `pasien_id` |

### 3.3 Folder & File
```
app/
 ├── Http/
 │    ├── Controllers/
 │    │     ├── Dinkes/
 │    │     └── Pasien/
 │    └── Middleware/
 ├── Models/
resources/
 ├── views/
 │    ├── components/
 │    └── dinkes/
 ├── js/
 │    ├── utils/
 │    └── dinkes/
 └── css/
database/
 ├── migrations/
 └── seeders/
```

---

## 4. Style & Formatting
- Gunakan **PSR-12** untuk PHP.  
- Indentasi 4 spasi.  
- 1 class per file.  
- Gunakan DocBlock pada method publik.  
- Komentar fokus pada *alasan*, bukan *deskripsi kode*.  
- Untuk Blade:
  ```blade
  @if($isActive)
      <x-button.primary>Aktif</x-button.primary>
  @endif
  ```
  Hindari logika berlapis dan loop kompleks.

---

## 5. Frontend (Vite, JS, Tailwind)
- **Dilarang menggunakan inline script.**  
  Semua script harus dimasukkan ke `resources/js/...` dan diload melalui `@vite()`.  
- Struktur direktori JS berdasarkan fitur:
  ```
  resources/js/
    ├── app.js
    ├── dropdown.js
    ├── dinkes/
    │    ├── dinkes-profile.js
    │    └── dinkes-dashboard.js
    └── utils/
         └── toast.js
  ```
- Gunakan **Tailwind CSS** melalui Vite (import di `app.css`).  
- Gunakan plugin Tailwind yang diperlukan saja (dark mode, forms).  
- Setiap interaksi DOM umum (dropdown, modal, toast) → buat utility JS modular.

---

## 6. Keamanan Aplikasi

### 6.1 CSP (Content Security Policy)
Tambahkan middleware `SecurityHeaders`:
```php
$script  = ["'self'"];
$style   = ["'self'", 'https://fonts.googleapis.com'];
$font    = ["'self'", 'https://fonts.gstatic.com'];
$img     = ["'self'", 'data:', 'blob:'];
$connect = ["'self'"];
```
> Saat local dev, tambahkan `http://localhost:5173` ke `connect-src` untuk HMR.

### 6.2 Validasi Input
- Gunakan Form Request Laravel (`StorePasienRequest`, `UpdateProfileRequest`).  
- Validasi semua input dari user.  
- Escape output (`{{ $var }}`) pada Blade.

### 6.3 SQL Injection & Path Traversal
- Gunakan Eloquent / Query Builder dengan binding.  
- Hindari `DB::selectRaw()` tanpa parameter.  
- Jangan pernah memproses path yang dikirim user langsung ke filesystem; gunakan Storage facade.

### 6.4 Autorisasi Role
Gunakan **Policy** atau **Gate** untuk setiap fitur (`dinkes`, `bidan`, `puskesmas`, `rs`, `pasien`).

---

## 7. Error Handling & Logging
- Tangkap exception di `app/Exceptions/Handler.php`.  
- Gunakan `abort(403)` / `abort(404)` untuk akses tidak sah.  
- Log ke `storage/logs/laravel.log`.  
- Jangan perlihatkan error stack trace di production.

---

## 8. Database & Migrations
- Semua perubahan skema harus melalui migration.  
- Gunakan `foreignId()->constrained()->cascadeOnDelete()` untuk integritas relasi.  
- Gunakan **Seeder** untuk data referensi (Role, Wilayah, Enum).  
- Mapping enum database ke Enum PHP agar tidak ada “magic string”.

---

## 9. Komponen Blade Rekomendasi
| Komponen | Fungsi | File |
|-----------|---------|------|
| `<x-layout.app>` | Template utama (kerangka dasbor) | `resources/views/components/layout/app.blade.php` |
| `<x-sidebar.menu>` | Sidebar per-role | `components/sidebar/menu.blade.php` |
| `<x-navbar.profile-menu>` | Dropdown profil + logout | `components/navbar/profile-menu.blade.php` |
| `<x-alert.flash>` | Flash message auto-dismiss | `components/alert/flash.blade.php` |
| `<x-card>` | Kartu konten dasbor | `components/card.blade.php` |
| `<x-table.simple>` | Tabel list data | `components/table/simple.blade.php` |

> 💡 Jika disetujui, setiap komponen diatur dengan props dan slot agar reusable.

---

## 10. Testing & Quality Assurance
- Gunakan **PHPUnit** atau **Pest** untuk unit dan feature test.  
- Tes utama:
  1. Login / logout setiap role.  
  2. CRUD Pasien & Skrining.  
  3. Statistik Dasbor.  
  4. Hak akses per role.  
- Jalankan `php artisan test` sebelum merge.

---

## 11. Git Workflow
- **Branching**:
  - `main` → stabil (untuk deploy).  
  - `develop` → pengembangan aktif.  
  - `feature/*` → fitur baru.  
  - `fix/*` → bug fix.  
- **Commit Message Format**:
  ```
  feat: tambah modul skrining pasien
  fix: perbaiki CSP font-src
  chore: update composer dependencies
  ```
- Review via Pull Request sebelum merge ke main.

---

## 12. Dokumentasi
- Simpan di `/docs` atau Wiki repo.  
- Gunakan diagram PlantUML / Mermaid untuk class & ERD.  
- Setiap service memiliki README.md mini (alur, param, return).  
- Update dokumentasi setiap fitur baru atau perubahan database.

---

## 13. Aksesibilitas & UI/UX
- Sertakan `alt` pada gambar.  
- Gunakan kontras warna yang memadai.  
- Komponen interaktif harus dapat dioperasikan keyboard (Escape, Tab, Enter).  
- Responsif mobile minimum lebar 360 px.

---

## 14. Performa & Optimisasi
- Hindari **N+1 Query** (`with()` / `load()` Eloquent).  
- Gunakan cache untuk data statistik berulang.  
- Jalankan `npm run build` saat production (Vite minify & hash).  
- Pastikan Tailwind `content` path sesuai agar purge berfungsi.

---

## 15. Lampiran (Referensi Singkat)
- **PSR-12 Standard**: <https://www.php-fig.org/psr/psr-12/>  
- **Laravel Coding Style**: <https://laravel.com/docs/master/contributions#coding-style>  
- **OWASP Top 10 (2021)** → SQL Injection, CSP, XSS Guidelines  
- **Tailwind Docs**: <https://tailwindcss.com/docs>

---

## 16. Penutup
Pedoman ini bersifat hidup dan wajib dipatuhi selama pengembangan DeLISA.  
Perubahan atau penambahan harus dibahas bersama tim teknis sebelum dimasukkan ke versi baru dokumen ini.

---
