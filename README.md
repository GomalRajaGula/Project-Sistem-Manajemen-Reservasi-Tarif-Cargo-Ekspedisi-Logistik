# CargoFlow - Sistem Manajemen Reservasi & Tarif Cargo Ekspedisi Logistik

Aplikasi web berbasis **PHP Native OOP** (Object-Oriented Programming) untuk mengelola reservasi pengiriman cargo, perhitungan tarif secara dinamis, dan validasi Standar Operasional Prosedur (SOP) packing pada perusahaan ekspedisi logistik.

---

## 🚀 Fitur Utama

- **Dashboard Real-Time**: Ringkasan data reservasi, pendapatan total, dan status packing cargo.
- **Dinamisasi Jenis Cargo**: Mendukung tiga jenis cargo dengan karakteristik, validasi SOP, dan formula tarif yang berbeda.
- **Kalkulator Tarif & SOP**: Simulasi perhitungan tarif dan kelayakan SOP packing sebelum reservasi disimpan.
- **Manajemen Reservasi & Laporan**: Penyimpanan transaksi ke database relasional dengan pencatatan struk detail.

---

## 🛠️ Konsep OOP yang Diimplementasikan

Aplikasi ini dirancang dengan menerapkan pilar-pilar utama **Pemrograman Berorientasi Objek (OOP)** pada PHP untuk menjamin kode yang *clean*, modular, dan mudah dikembangkan (*scalable*).

### 1. Abstraksi (Abstraction)
Abstraksi diterapkan dengan membuat kelas induk `Kargo` sebagai **`abstract class`**. Kelas ini mendefinisikan struktur dasar kargo tetapi tidak dapat diinstansiasi secara langsung.
* Kelas ini memiliki **`abstract method`** seperti `hitungTarifPengiriman()`, `validasiSOPPacking()`, dan `getRincianPerhitungan()`.
* Implementasi detail dari metode-metode tersebut disembunyikan dari kelas induk dan wajib diimplementasikan (*overridden*) di dalam masing-masing subclass.

### 2. Enkapsulasi (Encapsulation)
Enkapsulasi digunakan untuk melindungi data sensitif dari modifikasi luar secara langsung.
* Semua atribut utama dalam kelas `Kargo` (seperti `$id_resi`, `$pengirim`, `$beratBarang`, dll) serta subclass menggunakan hak akses **`protected`** atau **`private`**.
* Akses dan manipulasi data atribut dilakukan secara aman melalui metode perantara **Getter & Setter** (misalnya `getPengirim()`, `setPengirim()`, dll).

### 3. Pewarisan (Inheritance)
Pewarisan digunakan untuk meminimalkan redundansi kode.
* Kelas **`KargoReguler`**, **`KargoBahanKimia`**, dan **`KargoPecahBelah`** bertindak sebagai *subclass* yang mewarisi (*extends*) seluruh properti dan metode dari *superclass* **`Kargo`**.
* Atribut umum seperti nama pengirim, kota tujuan, dan berat barang cukup ditulis sekali di kelas induk.

### 4. Polimorfisme (Polymorphism)
Polimorfisme memungkinkan objek dari subclass yang berbeda diperlakukan sebagai objek dari superclass yang sama namun berperilaku sesuai karakternya masing-masing.
* Contoh nyata adalah pemanggilan metode `hitungTarifPengiriman()` dan `validasiSOPPacking()`. 
* Kelas **`KargoFactory`** cukup memanggil metode polimorfik ini secara seragam untuk mendapatkan hasil perhitungan yang berbeda sesuai dengan tipe instansiasi objek kargo yang aktif.

---

## 📐 Arsitektur OOP & Aturan Bisnis

### Struktur Kelas & Atribut

```
                      ┌─────────────────────────────────────┐
                      │            <<abstract>>             │
                      │                Kargo                │
                      ├─────────────────────────────────────┤
                      │ # id_resi: string                   │
                      │ # pengirim: string                  │
                      │ # kotaTujuan: string                │
                      │ # beratBarang: float                │
                      │ # tarifDasarPerKg: float            │
                      ├─────────────────────────────────────┤
                      │ + generateIdResi(prefix)            │
                      │ + simpanKargo(jenisKargo, pdo)      │
                      │ # hitungTarifPengiriman()*          │
                      │ # validasiSOPPacking()*             │
                      │ + getRincianPerhitungan()*          │
                      └──────────────────┬──────────────────┘
                                         │
                 ┌───────────────────────┼───────────────────────┐
                 │                       │                       │
      ┌──────────▼──────────┐ ┌──────────▼──────────┐ ┌──────────▼──────────┐
      │     KargoReguler    │ │   KargoBahanKimia   │ │   KargoPecahBelah   │
      ├─────────────────────┤ ├─────────────────────┤ ├─────────────────────┤
      │ # jenisPaket: string│ │ # tingkatBahaya: int│ │ # ketebalanBubble:  │
      │ # estimasiHari: int │ │ # sertifikasi: str  │ │   string            │
      │                     │ │                     │ │ # asuransi: float   │
      └─────────────────────┘ └─────────────────────┘ └─────────────────────┘
```

#### 1. Kelas Induk: `Kargo` (Abstract)
* **Atribut**:
  * `$id_resi` (Protected): Nomor resi unik transaksi.
  * `$pengirim` (Protected): Nama pengirim kargo.
  * `$kotaTujuan` (Protected): Kota destinasi pengiriman.
  * `$beratBarang` (Protected): Berat barang dalam kilogram (kg).
  * `$tarifDasarPerKg` (Protected): Tarif dasar pengiriman per kg.

#### 2. Subclass: `KargoReguler`
* **Atribut Tambahan**:
  * `$jenisPaket` (Protected): Tipe kemasan (`Koli` atau `Dus`).
  * `$estimasiHari` (Protected): Lama estimasi pengiriman.
* **Prefix ID Resi**: `REG`

#### 3. Subclass: `KargoBahanKimia`
* **Atribut Tambahan**:
  * `$tingkatBahaya` (Protected): Kelas bahaya kimia skala 1 s.d. 9.
  * `$jenisSertifikasiSandi` (Protected): Kode sertifikat kelayakan transportasi bahan kimia.
* **Prefix ID Resi**: `KIM`

#### 4. Subclass: `KargoPecahBelah`
* **Atribut Tambahan**:
  * `$ketebalanBubbleWrap` (Protected): Ketebalan pelindung (misal: "3 lapis").
  * `$biayaAsuransiWajib` (Protected): Nominal asuransi barang pecah belah.
* **Prefix ID Resi**: `PB`

### Aturan Bisnis Rumus Tarif Otomatis

Aplikasi menetapkan tarif dasar flat sebesar **Rp 10.000 / kg** yang dikelola terpusat di `KargoFactory.php`. Rumus akhir tarif dihitung secara otomatis berdasarkan aturan khusus subclass:

> **📦 Kargo Reguler**
> 
> $$\text{Total Tarif} = \text{Berat Barang} \times \text{Tarif Dasar}$$
> * *Prefix Resi*: `REG`
> * *SOP Packing*: Berat > 0 kg & tipe paket harus `Koli` atau `Dus`.

---

> **☣️ Kargo Bahan Kimia**
> 
> $$\text{Total Tarif} = (\text{Berat Barang} \times \text{Tarif Dasar}) + (\text{Tingkat Bahaya} \times \text{Rp 100.000})$$
> * *Prefix Resi*: `KIM`
> * *SOP Packing*: Berat > 0 kg, tingkat bahaya berada di rentang 1 s.d. 9, dan nomor sertifikasi terisi.

---

> **🔮 Kargo Pecah Belah**
> 
> $$\text{Total Tarif} = (\text{Berat Barang} \times \text{Tarif Dasar}) + \text{Asuransi Wajib (Rp 20.000)} + \text{Surcharge Fragile (5\% dari Tarif Berat)}$$
> * *Prefix Resi*: `PB`
> * *Asuransi Wajib*: Dipatok tetap Rp 20.000 di sisi backend.
> * *Surcharge Fragile*: Tambahan biaya 5% dari perkalian berat dan tarif dasar.
> * *SOP Packing*: Berat > 0 kg, ketebalan bubble wrap minimal 2 lapis, asuransi wajib memenuhi minimal Rp 5.000.

---

## 📁 Struktur Folder Project

Berikut adalah struktur direktori lengkap dari project **CargoFlow**:

```
Project-Sistem-Manajemen-Reservasi-Tarif-Cargo-Ekspedisi-Logistik/
├── assets/
│   └── css/
│       └── style.css                 # File styling utama halaman dashboard
├── classes/
│   ├── Kargo.php                     # Abstract Class (Parent) utama kargo
│   ├── KargoReguler.php              # Subclass khusus penanganan Kargo Reguler
│   ├── KargoBahanKimia.php           # Subclass khusus penanganan Kargo Bahan Kimia
│   ├── KargoPecahBelah.php           # Subclass khusus penanganan Kargo Pecah Belah
│   └── KargoFactory.php              # Class Design Pattern Factory untuk instansiasi objek
├── config/
│   ├── database.php                  # Class Database utility dengan koneksi PDO
│   └── connection.php                # File inisialisasi koneksi database procedural
├── dashboard/
│   ├── index.php                     # Halaman Dashboard & statistik transaksi
│   ├── reservasi.php                 # Halaman pembuatan reservasi pengiriman cargo baru
│   ├── perhitungan_tarif.php         # Kalkulator simulasi tarif dan validasi SOP
│   ├── kargo_reguler.php             # Halaman daftar pengiriman kargo reguler
│   ├── kargo_kimia.php               # Halaman daftar pengiriman kargo bahan kimia
│   ├── kargo_pecah_belah.php         # Halaman daftar pengiriman kargo pecah belah
│   ├── laporan.php                   # Halaman cetak laporan transaksi (print-friendly)
│   ├── navbar.php                    # Fragment komponen navigasi atas
│   └── sidebar.php                   # Fragment komponen navigasi samping
├── database/
│   └── schema.sql                    # SQL Script database schema (DDL)
└── README.md                         # Dokumentasi teknis project
```

---

## 🗄️ Skema Database

Aplikasi dikoneksikan ke database MySQL/MariaDB dengan konfigurasi database bernama **`db_logistik_cargo`** (atau dapat disesuaikan pada konfigurasi koneksi `'db'`). 

Berikut adalah skema DDL SQL ringkas untuk 4 tabel relasional yang digunakan:

```sql
-- 1. Tabel Utama / Induk Kargo
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Detail Kargo Reguler
CREATE TABLE kargo_reguler (
    id_resi VARCHAR(50) PRIMARY KEY,
    jenis_paket ENUM('Koli', 'Dus') NOT NULL,
    estimasi_hari INT NOT NULL,
    FOREIGN KEY (id_resi) REFERENCES kargo(id_resi) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Detail Kargo Bahan Kimia
CREATE TABLE kargo_bahan_kimia (
    id_resi VARCHAR(50) PRIMARY KEY,
    tingkat_bahaya INT NOT NULL,
    jenis_sertifikasi_sandi VARCHAR(100) NOT NULL,
    FOREIGN KEY (id_resi) REFERENCES kargo(id_resi) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Detail Kargo Pecah Belah
CREATE TABLE kargo_pecah_belah (
    id_resi VARCHAR(50) PRIMARY KEY,
    ketebalan_bubble_wrap INT NOT NULL, -- Diambil bagian angkanya saja (misal: 3)
    biaya_asuransi_wajib DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (id_resi) REFERENCES kargo(id_resi) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## ⚙️ Panduan Instalasi & Setup (Laragon)

Ikuti langkah-langkah di bawah ini untuk menjalankan project CargoFlow pada web server lokal Anda menggunakan **Laragon**:

1. **Pindahkan Folder Project**  
   Ekstrak dan pindahkan folder project `Project-Sistem-Manajemen-Reservasi-Tarif-Cargo-Ekspedisi-Logistik` ke dalam direktori root Laragon Anda.  
   * Biasanya berada di: `C:\laragon\www\`  
   * Atau dapat diletakkan di workspace Anda jika menggunakan virtual host: `Documents/PROJECT PBO UAS KEL 3`

2. **Jalankan Web Server**  
   Buka aplikasi Laragon, kemudian klik tombol **Start All** untuk menyalakan Apache dan MySQL.

3. **Import Database**  
   * Buka browser dan akses **phpMyAdmin** melalui url `http://localhost/phpmyadmin/` atau tekan tombol **Database** di Laragon.
   * Buat database baru dengan nama `db_logistik_cargo`.
   * Klik menu **Import**, pilih file SQL schema yang berada di `database/schema.sql` (atau salin isi DDL di atas dan jalankan pada tab **SQL**), lalu klik **Go**.

4. **Validasi Koneksi Database**  
   Anda dapat melakukan verifikasi koneksi dengan mengakses script pengetesan koneksi di browser:
   ```
   http://localhost/Project-Sistem-Manajemen-Reservasi-Tarif-Cargo-Ekspedisi-Logistik/config/connection.php
   ```
   *Jika layar kosong/tidak memunculkan error, berarti koneksi database berhasil terhubung.*

5. **Akses Dashboard Aplikasi**  
   Buka dashboard utama CargoFlow melalui tautan berikut:
   ```
   http://localhost/Project-Sistem-Manajemen-Reservasi-Tarif-Cargo-Ekspedisi-Logistik/dashboard/
   ```

---

## 👥 Tim Pengembang (Kelompok 3)

Project ini disusun sebagai tugas Ujian Akhir Semester (UAS) mata kuliah Pemrograman Berorientasi Objek (PBO).

| Foto / No | Nama Anggota | NIM | Peran dalam Proyek |
| :---: | :--- | :---: | :--- |
| 1 | [Nama Anggota 1] | [NIM Anggota 1] | Backend Developer / Database Designer |
| 2 | [Nama Anggota 2] | [NIM Anggota 2] | Frontend Developer / UI Designer |
| 3 | [Nama Anggota 3] | [NIM Anggota 3] | System Analyst / Tester |
| 4 | [Nama Anggota 4] | [NIM Anggota 4] | Technical Writer / Documentation |

---

## 📄 Lisensi

Project ini dibuat khusus untuk memenuhi nilai akademik pada Program Studi Informatika/Sistem Informasi. Hak Cipta dilindungi undang-undang kelompok.
