# Sistem Manajemen Reservasi & Tarif Cargo Ekspedisi Logistik

Aplikasi web berbasis **PHP murni (Native PHP)** dengan konsep **Object-Oriented Programming (OOP)** untuk mengelola reservasi pengiriman cargo, perhitungan tarif, dan validasi SOP packing pada perusahaan ekspedisi logistik.

> **Mata Kuliah:** Pemrograman Berorientasi Objek (PBO) — UAS Kelompok 3

---

## Fitur Utama

- **Dashboard** — Ringkasan statistik pengiriman dan pendapatan
- **Manajemen Kargo** — Tiga jenis kargo dengan aturan bisnis berbeda:
  - Kargo Reguler (Koli/Dus)
  - Kargo Bahan Kimia (Class 1–9)
  - Kargo Pecah Belah (bubble wrap + asuransi)
- **Reservasi Pengiriman** — Form input reservasi berdasarkan jenis kargo
- **Perhitungan Tarif** — Kalkulasi otomatis sesuai rumus masing-masing subclass
- **Laporan** — Tampilan data pengiriman

---

## Teknologi

| Komponen | Teknologi |
|---|---|
| Backend | PHP 7.4+ (Native, OOP) |
| Database | MySQL / MariaDB |
| Koneksi DB | PDO |
| Frontend | HTML, CSS, JavaScript |
| Server Lokal | Laragon (recommended) |

---

## Struktur Proyek

```
Project-Sistem-Manajemen-Reservasi-Tarif-Cargo-Ekspedisi-Logistik/
├── assets/
│   └── css/
│       └── style.css              # Stylesheet dashboard
├── classes/
│   ├── Kargo.php                  # Abstract class (parent)
│   ├── KargoReguler.php           # Subclass kargo reguler
│   ├── KargoBahanKimia.php        # Subclass kargo bahan kimia
│   └── KargoPecahBelah.php        # Subclass kargo pecah/belah
├── config/
│   ├── database.php               # Class Database (PDO)
│   └── connection.php             # Tes koneksi database
├── dashboard/
│   ├── index.php                  # Halaman utama dashboard
│   ├── kargo_reguler.php          # Data kargo reguler
│   ├── kargo_kimia.php            # Data kargo bahan kimia
│   ├── kargo_pecah_belah.php      # Data kargo pecah belah
│   ├── reservasi.php              # Form reservasi pengiriman
│   ├── perhitungan_tarif.php      # Kalkulasi tarif
│   ├── laporan.php                # Laporan pengiriman
│   ├── navbar.php                 # Komponen navbar
│   └── sidebar.php                # Komponen sidebar navigasi
└── README.md
```

---

## Arsitektur OOP

### Class Diagram (Konsep)

```
                    ┌─────────────────────┐
                    │   <<abstract>>      │
                    │       Kargo         │
                    ├─────────────────────┤
                    │ # id_resi           │
                    │ # pengirim          │
                    │ # kotaTujuan        │
                    │ # beratBarang       │
                    │ # tarifDasarPerKg   │
                    ├─────────────────────┤
                    │ + generateIdResi()  │
                    │ + simpanKargo()     │
                    │ # hitungTarif() *   │
                    │ # validasiSOP() *   │
                    └─────────┬───────────┘
                              │
          ┌───────────────────┼───────────────────┐
          │                   │                   │
┌─────────▼─────────┐ ┌───────▼────────┐ ┌────────▼──────────┐
│   KargoReguler    │ │ KargoBahanKimia│ │  KargoPecahBelah  │
│   prefix: REG     │ │  prefix: KIM   │ │   prefix: PB      │
└───────────────────┘ └────────────────┘ └───────────────────┘
```

### Abstract Class `Kargo`

| Atribut | Tipe | Keterangan |
|---|---|---|
| `id_resi` | string | ID resi unik |
| `pengirim` | string | Nama pengirim |
| `kotaTujuan` | string | Kota tujuan |
| `beratBarang` | float | Berat dalam kg |
| `tarifDasarPerKg` | float | Tarif dasar per kg |

| Method | Keterangan |
|---|---|
| `generateIdResi($prefix)` | Generate ID: `PREFIX + YmdHis + 4 digit random` |
| `simpanKargo($jenisKargo)` | Insert ke tabel `kargo` |
| `hitungTarifPengiriman()` | **Abstract** — di-override tiap subclass |
| `validasiSOPPacking()` | **Abstract** — di-override tiap subclass |

### Subclass & Aturan Bisnis

#### 1. `KargoReguler` — Prefix: `REG`

| Atribut Tambahan | Keterangan |
|---|---|
| `jenisPaket` | `Koli` atau `Dus` |
| `estimasiHari` | Estimasi hari pengiriman |

- **Rumus tarif:** `Berat × Tarif Dasar per Kg`
- **Validasi SOP:** Berat > 0, jenis paket valid
- **Tabel child:** `kargo_reguler`
- **Method simpan:** `simpanKargoReguler()`

#### 2. `KargoBahanKimia` — Prefix: `KIM`

| Atribut Tambahan | Keterangan |
|---|---|
| `tingkatBahaya` | Class 1–9 |
| `jenisSertifikasiSandi` | Kode sertifikasi keamanan |

- **Rumus tarif:** `Berat × Tarif Dasar × Multiplier`
  - Class 1–3 → ×1.2
  - Class 4–6 → ×1.5
  - Class 7–9 → ×2.0
- **Validasi SOP:** Berat > 0, tingkat bahaya valid, sertifikasi terisi
- **Tabel child:** `kargo_bahan_kimia`
- **Method simpan:** `simpanKargoBahanKimia()`

#### 3. `KargoPecahBelah` — Prefix: `PB`

| Atribut Tambahan | Keterangan |
|---|---|
| `ketebalanBubbleWrap` | Contoh: `"3 lapis"` |
| `biayaAsuransiWajib` | Minimal Rp 5.000 |

- **Rumus tarif:** `(Berat × Tarif Dasar) + Biaya Asuransi`
- **Validasi SOP:** Berat > 0, bubble wrap ≥ 2 lapis, asuransi ≥ Rp 5.000
- **Tabel child:** `kargo_pecah_belah`
- **Method simpan:** `simpanKargoPecahBelah()`

---

## Database

**Nama database:** `db_logistik_cargo`

### Konfigurasi Koneksi

Edit `config/database.php` jika diperlukan:

| Parameter | Default |
|---|---|
| Host | `localhost` |
| Username | `root` |
| Password | *(kosong)* |
| Database | `db_logistik_cargo` |

### Skema Tabel

```sql
-- Tabel induk
CREATE TABLE kargo (
    id_resi VARCHAR(50) PRIMARY KEY,
    pengirim VARCHAR(100) NOT NULL,
    kota_tujuan VARCHAR(100) NOT NULL,
    berat_barang DECIMAL(10,2) NOT NULL,
    tarif_dasar_per_kg DECIMAL(10,2) NOT NULL,
    jenis_kargo VARCHAR(50) NOT NULL,
    total_tarif DECIMAL(15,2) NOT NULL,
    status_packing ENUM('TERPENUHI', 'BELUM TERPENUHI') NOT NULL,
    tanggal_reservasi DATETIME NOT NULL
);

-- Kargo Reguler
CREATE TABLE kargo_reguler (
    id_resi VARCHAR(50) PRIMARY KEY,
    jenis_paket ENUM('Koli', 'Dus') NOT NULL,
    estimasi_hari INT NOT NULL,
    FOREIGN KEY (id_resi) REFERENCES kargo(id_resi)
);

-- Kargo Bahan Kimia
CREATE TABLE kargo_bahan_kimia (
    id_resi VARCHAR(50) PRIMARY KEY,
    tingkat_bahaya INT NOT NULL,
    jenis_sertifikasi_sandi VARCHAR(100) NOT NULL,
    FOREIGN KEY (id_resi) REFERENCES kargo(id_resi)
);

-- Kargo Pecah Belah
CREATE TABLE kargo_pecah_belah (
    id_resi VARCHAR(50) PRIMARY KEY,
    ketebalan_bubble_wrap VARCHAR(50) NOT NULL,
    biaya_asuransi_wajib DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (id_resi) REFERENCES kargo(id_resi)
);
```

---

## Instalasi & Menjalankan

### Prasyarat

- [Laragon](https://laragon.org/) (atau XAMPP/WAMP)
- PHP 7.4 atau lebih baru
- MySQL / MariaDB
- Browser modern

### Langkah Instalasi

1. **Clone / copy** project ke folder web server Laragon:
   ```
   C:\laragon\www\Project-Sistem-Manajemen-Reservasi-Tarif-Cargo-Ekspedisi-Logistik
   ```

2. **Jalankan Laragon** — pastikan Apache dan MySQL aktif (status hijau).

3. **Buat database** di phpMyAdmin:
   - Buka `http://localhost/phpmyadmin`
   - Buat database baru: `db_logistik_cargo`
   - Jalankan skema SQL di atas

4. **Tes koneksi database:**
   ```
   http://localhost/Project-Sistem-Manajemen-Reservasi-Tarif-Cargo-Ekspedisi-Logistik/config/connection.php
   ```
   Pastikan muncul pesan koneksi berhasil.

5. **Buka dashboard aplikasi:**
   ```
   http://localhost/Project-Sistem-Manajemen-Reservasi-Tarif-Cargo-Ekspedisi-Logistik/dashboard/
   ```

---

## Contoh Penggunaan Class

```php
<?php
require_once 'classes/KargoReguler.php';

$kargo = new KargoReguler();

// Set data umum
$kargo->setPengirim('PT. Logistik Jaya');
$kargo->setKotaTujuan('Surabaya');
$kargo->setBeratBarang(12.5);
$kargo->setTarifDasarPerKg(5000);

// Set data spesifik kargo reguler
$kargo->setJenisPaket('Koli');
$kargo->setEstimasiHari(3);

// Simpan: induk dulu, lalu child
$kargo->generateIdResi('REG');
$kargo->simpanKargo('Reguler');
$kargo->simpanKargoReguler();
```

---

## Konsep OOP yang Diterapkan

| Konsep | Implementasi |
|---|---|
| **Abstraksi** | Class `Kargo` abstrak — method tarif & validasi disembunyikan di subclass |
| **Enkapsulasi** | Atribut `protected`, akses via getter/setter |
| **Inheritance** | `KargoReguler`, `KargoBahanKimia`, `KargoPecahBelah` extends `Kargo` |
| **Polimorfisme** | `hitungTarifPengiriman()` dan `validasiSOPPacking()` berbeda tiap subclass |

---

## Tim Pengembang

Kelompok 3 — Pemrograman Berorientasi Objek (PBO) UAS

---

## Lisensi

Project akademik untuk keperluan Ujian Akhir Semester (UAS) mata kuliah Pemrograman Berorientasi Objek.
