<?php

require_once __DIR__ . '/Kargo.php';

/**
 * Class KargoBahanKimia
 *
 * Subclass dari abstract class Kargo untuk pengiriman cargo berupa bahan kimia.
 * Menangani validasi tingkat bahaya dan sertifikasi keamanan khusus untuk bahan berbahaya.
 */
class KargoBahanKimia extends Kargo
{
    /** @var int Tingkat bahaya bahan kimia (rentang Class 1 - 9 sesuai standar international) */
    protected $tingkatBahaya;

    /** @var string Jenis/kode sertifikasi keamanan bahan kimia */
    protected $jenisSertifikasiSandi;

    /** @var string Prefix ID resi khusus kargo bahan kimia */
    private const PREFIX_ID_RESI = 'KIM';

    /** @var array Daftar tingkat bahaya yang valid (Class 1 - 9) */
    private const TINGKAT_BAHAYA_VALID = [1, 2, 3, 4, 5, 6, 7, 8, 9];

    /**
     * Constructor untuk KargoBahanKimia.
     *
     * @param string $pengirim Nama pengirim kargo
     * @param string $kotaTujuan Kota tujuan pengiriman
     * @param float $beratBarang Berat barang dalam kilogram
     * @param float $tarifDasarPerKg Tarif dasar per kilogram
     * @param int $tingkatBahaya Tingkat bahaya bahan kimia (1-9)
     * @param string $jenisSertifikasiSandi Kode/nama sertifikasi keamanan
     */
    public function __construct(
        string $pengirim,
        string $kotaTujuan,
        float $beratBarang,
        float $tarifDasarPerKg,
        int $tingkatBahaya,
        string $jenisSertifikasiSandi
    ) {
        $this->pengirim = $pengirim;
        $this->kotaTujuan = $kotaTujuan;
        $this->beratBarang = $beratBarang;
        $this->tarifDasarPerKg = $tarifDasarPerKg;
        $this->tingkatBahaya = $tingkatBahaya;
        $this->jenisSertifikasiSandi = $jenisSertifikasiSandi;
    }

    /**
     * Menghitung total tarif pengiriman bahan kimia.
     * Rumus: Berat (kg) × Tarif Dasar per Kg × Multiplier Bahaya
     * Multiplier Bahaya ditentukan berdasarkan tingkat bahaya:
     * - Class 1-3 (rendah): 1.2x
     * - Class 4-6 (sedang): 1.5x
     * - Class 7-9 (tinggi): 2.0x
     *
     * @return float
     */
    protected function hitungTarifPengiriman(): float
    {
        $multiplierBahaya = $this->getMultiplierBahaya();
        return (float) $this->beratBarang * (float) $this->tarifDasarPerKg * $multiplierBahaya;
    }

    /**
     * Mendapatkan multiplier tarif berdasarkan tingkat bahaya.
     *
     * @return float
     */
    private function getMultiplierBahaya(): float
    {
        if ($this->tingkatBahaya >= 1 && $this->tingkatBahaya <= 3) {
            return 1.2;
        } elseif ($this->tingkatBahaya >= 4 && $this->tingkatBahaya <= 6) {
            return 1.5;
        } elseif ($this->tingkatBahaya >= 7 && $this->tingkatBahaya <= 9) {
            return 2.0;
        }
        return 1.0; // Default jika ada kesalahan
    }

    /**
     * Validasi SOP packing untuk bahan kimia.
     * Aturan:
     * 1. Berat barang harus lebih dari 0 kg
     * 2. Tingkat bahaya harus valid (Class 1-9)
     * 3. Jenis sertifikasi keamanan harus tidak kosong/terisi
     *
     * @return bool
     */
    protected function validasiSOPPacking(): bool
    {
        // Aturan 1: berat harus positif
        if ($this->beratBarang <= 0) {
            return false;
        }

        // Aturan 2: tingkat bahaya harus valid (Class 1-9)
        if (!in_array($this->tingkatBahaya, self::TINGKAT_BAHAYA_VALID, true)) {
            return false;
        }

        // Aturan 3: sertifikasi keamanan harus terisi
        if (empty($this->jenisSertifikasiSandi)) {
            return false;
        }

        return true;
    }

    /**
     * Simpan data spesifik bahan kimia ke tabel `kargo_bahan_kimia`.
     * Pastikan data induk sudah tersimpan di tabel `kargo` terlebih dahulu
     * agar foreign key id_resi terpenuhi.
     *
     * @return bool
     */
    public function simpanKargoBahanKimia(): bool
    {
        // Generate ID resi dengan prefix KIM jika belum ada
        if (empty($this->id_resi)) {
            $this->generateIdResi(self::PREFIX_ID_RESI);
        }

        try {
            $database = new Database();
            $pdo = $database->getConnection();

            $sql = "INSERT INTO kargo_bahan_kimia
                    (id_resi, tingkat_bahaya, jenis_sertifikasi_sandi)
                    VALUES
                    (:id_resi, :tingkat_bahaya, :jenis_sertifikasi_sandi)";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_resi', $this->id_resi);
            $stmt->bindValue(':tingkat_bahaya', $this->tingkatBahaya, PDO::PARAM_INT);
            $stmt->bindValue(':jenis_sertifikasi_sandi', $this->jenisSertifikasiSandi);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('KargoBahanKimia::simpanKargoBahanKimia error: ' . $e->getMessage());
            return false;
        }
    }

    // --- Setter & Getter untuk atribut tambahan ---

    /**
     * Set tingkat bahaya bahan kimia.
     *
     * @param int $tingkatBahaya Tingkat bahaya (Class 1-9)
     * @return void
     */
    public function setTingkatBahaya(int $tingkatBahaya): void
    {
        $this->tingkatBahaya = $tingkatBahaya;
    }

    /**
     * Get tingkat bahaya bahan kimia.
     *
     * @return int|null
     */
    public function getTingkatBahaya(): ?int
    {
        return $this->tingkatBahaya;
    }

    /**
     * Set jenis sertifikasi keamanan.
     *
     * @param string $jenisSertifikasiSandi Kode/nama sertifikasi
     * @return void
     */
    public function setJenisSertifikasiSandi(string $jenisSertifikasiSandi): void
    {
        $this->jenisSertifikasiSandi = $jenisSertifikasiSandi;
    }

    /**
     * Get jenis sertifikasi keamanan.
     *
     * @return string|null
     */
    public function getJenisSertifikasiSandi(): ?string
    {
        return $this->jenisSertifikasiSandi;
    }
}
