-- DDL Schema untuk database db_logistik_cargo
-- Digunakan untuk proyek UAS Pemrograman Berorientasi Objek (PBO) - CargoFlow

CREATE DATABASE IF NOT EXISTS db_logistik_cargo;
USE db_logistik_cargo;

-- 1. Tabel Utama / Induk Kargo
CREATE TABLE IF NOT EXISTS kargo (
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
CREATE TABLE IF NOT EXISTS kargo_reguler (
    id_resi VARCHAR(50) PRIMARY KEY,
    jenis_paket ENUM('Koli', 'Dus') NOT NULL,
    estimasi_hari INT NOT NULL,
    FOREIGN KEY (id_resi) REFERENCES kargo(id_resi) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Detail Kargo Bahan Kimia
CREATE TABLE IF NOT EXISTS kargo_bahan_kimia (
    id_resi VARCHAR(50) PRIMARY KEY,
    tingkat_bahaya INT NOT NULL,
    jenis_sertifikasi_sandi VARCHAR(100) NOT NULL,
    FOREIGN KEY (id_resi) REFERENCES kargo(id_resi) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Detail Kargo Pecah Belah
CREATE TABLE IF NOT EXISTS kargo_pecah_belah (
    id_resi VARCHAR(50) PRIMARY KEY,
    ketebalan_bubble_wrap INT NOT NULL,
    biaya_asuransi_wajib DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (id_resi) REFERENCES kargo(id_resi) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
