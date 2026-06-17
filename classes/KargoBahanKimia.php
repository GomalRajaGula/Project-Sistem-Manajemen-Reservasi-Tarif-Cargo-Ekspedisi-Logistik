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

    /** @var float Biaya tambahan per tingkat bahaya (Class × Rp100.000) */
    private const BIAYA_PER_TINGKAT_BAHAYA = 100000;

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
     * Rumus: (Berat × Tarif Dasar per Kg) + (Tingkat Bahaya × Rp100.000)
     *
     * @return float
     */
    protected function hitungTarifPengiriman(): float
    {
        $tarifBerat = (float) $this->beratBarang * (float) $this->tarifDasarPerKg;
        $biayaBahaya = (float) $this->tingkatBahaya * self::BIAYA_PER_TINGKAT_BAHAYA;

        return $tarifBerat + $biayaBahaya;
    }

    /**
     * Rincian komponen tarif untuk ditampilkan di dashboard.
     *
     * @return array{tarif_berat: float, biaya_bahaya: float, formula: string}
     */
    public function getRincianPerhitungan(): array
    {
        $tarifBerat = (float) $this->beratBarang * (float) $this->tarifDasarPerKg;
        $biayaBahaya = (float) $this->tingkatBahaya * self::BIAYA_PER_TINGKAT_BAHAYA;

        return [
            'tarif_berat' => $tarifBerat,
            'biaya_bahaya' => $biayaBahaya,
            'formula' => sprintf(
                '(%s kg × Rp %s) + (Class %d × Rp %s)',
                $this->beratBarang,
                number_format($this->tarifDasarPerKg, 0, ',', '.'),
                $this->tingkatBahaya,
                number_format(self::BIAYA_PER_TINGKAT_BAHAYA, 0, ',', '.')
            ),
        ];
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
    public function simpanKargoBahanKimia(?PDO $pdo = null): bool
    {
        // Generate ID resi dengan prefix KIM jika belum ada
        if (empty($this->id_resi)) {
            $this->generateIdResi(self::PREFIX_ID_RESI);
        }

        try {
            if ($pdo === null) {
                require __DIR__ . '/../config/connection.php';
            }

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
