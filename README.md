<div align="center">

# 💰 Sistem Monitoring Gaji Karyawan

**Aplikasi web modern untuk mengelola dan memonitor penggajian karyawan secara efisien**

[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![CSS3](https://img.shields.io/badge/CSS3-Custom-1572B6?style=for-the-badge&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

[Demo](#) · [Fitur](#-fitur) · [Instalasi](#-instalasi) · [Dokumentasi](#-struktur-proyek)

</div>

---

## 📋 Tentang Proyek

**Sistem Monitoring Gaji Karyawan** adalah aplikasi web berbasis **Laravel 13** yang dirancang untuk memudahkan pengelolaan data karyawan dan penggajian perusahaan. Dibangun dengan pendekatan **Single Page Application (SPA)** menggunakan **Vanilla JavaScript + AJAX**, sehingga semua operasi berjalan tanpa reload halaman.

Aplikasi ini cocok untuk perusahaan kecil hingga menengah yang membutuhkan sistem penggajian yang sederhana namun lengkap.

---

## ✨ Fitur

### 📊 Dashboard
- Statistik ringkasan: total karyawan aktif, total gaji bulan ini, jumlah yang sudah/belum dibayar
- Grafik batang interaktif statistik gaji **12 bulan terakhir** (Chart.js)
- Daftar **Top 5 gaji tertinggi** bulan berjalan dengan ranking medal

### 👥 Manajemen Karyawan
- CRUD lengkap data karyawan (tambah, lihat, edit, hapus)
- Pencarian **real-time** berdasarkan nama, NIK, jabatan, atau departemen
- Manajemen status karyawan (Aktif / Non-Aktif)

### 💵 Manajemen Gaji
- Input komponen gaji secara detail:
  - **Pendapatan**: Gaji Pokok, Tunjangan Transport, Tunjangan Makan, Tunjangan Lainnya, Bonus
  - **Potongan**: BPJS, Pajak (PPh21), Potongan Lainnya
- **Kalkulasi otomatis** gaji bersih dengan preview real-time saat input
- Filter data gaji berdasarkan bulan, tahun, dan status pembayaran
- Manajemen status bayar (Sudah Bayar / Belum Bayar)
- **Slip gaji** yang bisa dicetak langsung dari browser

### 🎨 UI/UX
- Desain modern dengan sidebar yang bisa di-collapse
- Notifikasi toast untuk setiap aksi (berhasil/gagal)
- Modal konfirmasi sebelum menghapus data
- Tampilan responsif untuk desktop dan mobile
- Print-friendly untuk slip gaji

---

## 🛠️ Teknologi

| Layer | Teknologi |
|---|---|
| Backend | PHP 8.3+, Laravel 13 |
| Frontend | HTML5, CSS3 (Custom), Vanilla JavaScript ES6+ |
| AJAX | Fetch API |
| Database | SQLite (default) / MySQL / PostgreSQL |
| Chart | Chart.js 4.x |
| Icons | Font Awesome 6.x |

---

## ⚙️ Instalasi

### Prasyarat
Pastikan sudah terinstall:
- **PHP** >= 8.3
- **Composer**
- **Node.js** & **NPM**

### Langkah Instalasi

**1. Clone repositori**
```bash
git clone https://github.com/SanVen-Blip/Gaji.git
cd Gaji
```

**2. Install dependensi PHP**
```bash
composer install
```

**3. Salin file environment**
```bash
cp .env.example .env
```

**4. Generate application key**
```bash
php artisan key:generate
```

**5. Konfigurasi database**

Secara default aplikasi menggunakan **SQLite**. Pastikan file database ada:
```bash
touch database/database.sqlite
```

Atau jika ingin menggunakan **MySQL**, edit file `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=password_anda
```

**6. Jalankan migrasi dan seeder**
```bash
php artisan migrate --seed
```
> Perintah ini akan membuat semua tabel dan mengisi **8 data karyawan** beserta **24 data gaji** contoh secara otomatis.

**7. Install dependensi frontend**
```bash
npm install
npm run build
```

**8. Jalankan server**
```bash
php artisan serve
```

Buka browser dan akses **http://localhost:8000** 🎉

---

## 📁 Struktur Proyek

```
├── app/
│   ├── Http/Controllers/
│   │   ├── KaryawanController.php   # CRUD karyawan
│   │   └── GajiController.php       # CRUD gaji + dashboard stats
│   └── Models/
│       ├── Karyawan.php             # Model karyawan + relasi
│       └── Gaji.php                 # Model gaji + helper methods
│
├── database/
│   ├── migrations/
│   │   ├── ..._create_karyawan_table.php
│   │   └── ..._create_gaji_table.php
│   └── seeders/
│       └── DatabaseSeeder.php       # Data contoh karyawan & gaji
│
├── routes/
│   └── web.php                      # Semua route web + API
│
├── resources/views/
│   └── app.blade.php                # Single Page Application view
│
├── public/
│   ├── css/app-gaji.css             # Stylesheet custom
│   └── js/app-gaji.js              # Logic AJAX + UI interactions
```

---

## 🔌 API Endpoints

### Karyawan
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/karyawan` | Ambil semua karyawan |
| POST | `/api/karyawan` | Tambah karyawan baru |
| GET | `/api/karyawan/{id}` | Ambil detail karyawan |
| PUT | `/api/karyawan/{id}` | Update data karyawan |
| DELETE | `/api/karyawan/{id}` | Hapus karyawan |

### Gaji
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/gaji/dashboard` | Data statistik dashboard |
| GET | `/api/gaji` | Ambil data gaji (support filter) |
| POST | `/api/gaji` | Tambah data gaji |
| GET | `/api/gaji/{id}` | Ambil detail gaji |
| PUT | `/api/gaji/{id}` | Update data gaji |
| DELETE | `/api/gaji/{id}` | Hapus data gaji |

**Query params untuk `GET /api/gaji`:**
```
?bulan=1&tahun=2025&status_bayar=belum_bayar&karyawan_id=1
```

---

## 🗄️ Struktur Database

### Tabel `karyawan`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| nik | string (unique) | Nomor Induk Karyawan |
| nama | string | Nama lengkap |
| jabatan | string | Posisi/jabatan |
| departemen | string | Divisi |
| email | string (unique) | Email karyawan |
| telepon | string (nullable) | Nomor HP |
| tanggal_masuk | date | Tanggal mulai kerja |
| status | enum | aktif / nonaktif |

### Tabel `gaji`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| karyawan_id | foreign key | Relasi ke tabel karyawan |
| bulan | integer | Bulan (1–12) |
| tahun | integer | Tahun |
| gaji_pokok | decimal | Gaji dasar |
| tunjangan_transport | decimal | Tunjangan transportasi |
| tunjangan_makan | decimal | Tunjangan makan |
| tunjangan_lainnya | decimal | Tunjangan tambahan |
| bonus | decimal | Bonus |
| potongan_bpjs | decimal | Potongan BPJS |
| potongan_pajak | decimal | Potongan PPh21 |
| potongan_lainnya | decimal | Potongan lain |
| gaji_bersih | decimal | Total gaji bersih |
| status_bayar | enum | belum_bayar / sudah_bayar |
| tanggal_bayar | date (nullable) | Tanggal realisasi bayar |
| keterangan | text (nullable) | Catatan |

> **Constraint:** Kombinasi `(karyawan_id, bulan, tahun)` bersifat unique — satu karyawan hanya bisa punya satu data gaji per periode.

---

## 📸 Screenshot

> *Tambahkan screenshot aplikasi di sini setelah deploy*

| Dashboard | Data Karyawan |
|-----------|---------------|
| ![Dashboard](https://via.placeholder.com/400x250?text=Dashboard) | ![Karyawan](https://via.placeholder.com/400x250?text=Data+Karyawan) |

| Data Gaji | Slip Gaji |
|-----------|-----------|
| ![Gaji](https://via.placeholder.com/400x250?text=Data+Gaji) | ![Slip](https://via.placeholder.com/400x250?text=Slip+Gaji) |

---

## 🤝 Kontribusi

Kontribusi sangat disambut! Silakan:

1. Fork repositori ini
2. Buat branch fitur baru (`git checkout -b fitur/nama-fitur`)
3. Commit perubahan (`git commit -m 'Tambah fitur X'`)
4. Push ke branch (`git push origin fitur/nama-fitur`)
5. Buat Pull Request

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

---

<div align="center">

Dibuat dengan ❤️ menggunakan Laravel & Vanilla JavaScript

⭐ **Jangan lupa beri bintang jika proyek ini membantu!** ⭐

</div>
