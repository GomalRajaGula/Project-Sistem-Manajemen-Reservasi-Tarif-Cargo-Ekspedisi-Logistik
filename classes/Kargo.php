<?php

require_once __DIR__ . '/../config/database.php';

abstract class Kargo
{
    // Atribut protected untuk enkapsulasi data.
    protected $id_resi;
    protected $pengirim;
    protected $kotaTujuan;
    protected $beratBarang;
    protected $tarifDasarPerKg;

    /**
     * Generate ID resi otomatis.
     * Format: PREFIX + YmdHis + 4 digit random.
     * Contoh: KGO202406111530450123
     *
     * @param string $prefix
     * @return string
     */
    public function generateIdResi(string $prefix): string
    {
        $timestamp = date('YmdHis');
        $randomDigits = str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $this->id_resi = strtoupper($prefix) . $timestamp . $randomDigits;

        return $this->id_resi;
    }

    /**
     * Simpan data kargo ke tabel `kargo`.
     * Menggunakan PDO dari config/database.php.
     *
     * @param string $jenisKargo
     * @return bool
     */
    public function simpanKargo(string $jenisKargo): bool
    {
        // Pastikan id_resi tersedia sebelum menyimpan.
        if (empty($this->id_resi)) {
            $this->generateIdResi('KGO');
        }

        // Hitung total tarif berdasarkan implementasi subclass.
        $totalTarif = $this->hitungTarifPengiriman();

        // Validasi SOP packing melalui implementasi subclass.
        $packingValid = $this->validasiSOPPacking();
        $statusPacking = $packingValid ? 'TERPENUHI' : 'BELUM TERPENUHI';

        try {
            $database = new Database();
            $pdo = $database->getConnection();

            $sql = "INSERT INTO kargo
                    (id_resi, pengirim, kota_tujuan, berat_barang, tarif_dasar_per_kg, jenis_kargo, total_tarif, status_packing, tanggal_reservasi)
                    VALUES
                    (:id_resi, :pengirim, :kota_tujuan, :berat_barang, :tarif_dasar_per_kg, :jenis_kargo, :total_tarif, :status_packing, :tanggal_reservasi)";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_resi', $this->id_resi);
            $stmt->bindValue(':pengirim', $this->pengirim);
            $stmt->bindValue(':kota_tujuan', $this->kotaTujuan);
            $stmt->bindValue(':berat_barang', $this->beratBarang);
            $stmt->bindValue(':tarif_dasar_per_kg', $this->tarifDasarPerKg);
            $stmt->bindValue(':jenis_kargo', $jenisKargo);
            $stmt->bindValue(':total_tarif', $totalTarif);
            $stmt->bindValue(':status_packing', $statusPacking);
            $stmt->bindValue(':tanggal_reservasi', date('Y-m-d H:i:s'));

            return $stmt->execute();
        } catch (PDOException $e) {
            // Jika diperlukan, log error atau tampilkan pesan debugging.
            error_log('Kargo::simpanKargo error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Metode abstrak untuk menghitung total tarif pengiriman.
     * Implementasi berbeda pada setiap subclass kargo.
     *
     * @return float
     */
    abstract protected function hitungTarifPengiriman(): float;

    /**
     * Metode abstrak untuk validasi SOP packing.
     * Implementasi berbeda pada setiap subclass kargo.
     *
     * @return bool
     */
    abstract protected function validasiSOPPacking(): bool;

    // Setter dan getter untuk semua atribut.

    public function setIdResi(string $idResi): void
    {
        $this->id_resi = $idResi;
    }

    public function getIdResi(): ?string
    {
        return $this->id_resi;
    }

    public function setPengirim(string $pengirim): void
    {
        $this->pengirim = $pengirim;
    }

    public function getPengirim(): ?string
    {
        return $this->pengirim;
    }

    public function setKotaTujuan(string $kotaTujuan): void
    {
        $this->kotaTujuan = $kotaTujuan;
    }

    public function getKotaTujuan(): ?string
    {
        return $this->kotaTujuan;
    }

    public function setBeratBarang(float $beratBarang): void
    {
        $this->beratBarang = $beratBarang;
    }

    public function getBeratBarang(): ?float
    {
        return $this->beratBarang;
    }

    public function setTarifDasarPerKg(float $tarifDasarPerKg): void
    {
        $this->tarifDasarPerKg = $tarifDasarPerKg;
    }

    public function getTarifDasarPerKg(): ?float
    {
        return $this->tarifDasarPerKg;
    }
}
