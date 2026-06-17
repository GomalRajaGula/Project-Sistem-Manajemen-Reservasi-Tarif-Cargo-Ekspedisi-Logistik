<?php

require_once __DIR__ . '/Kargo.php';

/**
 * Class KargoReguler
 *
 * Subclass dari abstract class Kargo untuk pengiriman cargo reguler.
 * Menggunakan kemasan Koli/Dus dengan estimasi hari pengiriman.
 */
class KargoReguler extends Kargo
{
    /** @var string Jenis paket: 'Koli' atau 'Dus' */
    protected $jenisPaket;

    /** @var int Estimasi lama pengiriman dalam hari */
    protected $estimasiHari;

    /** @var string Prefix ID resi khusus kargo reguler */
    private const PREFIX_ID_RESI = 'REG';

    /** @var array Daftar jenis paket yang valid sesuai ENUM di database */
    private const JENIS_PAKET_VALID = ['Koli', 'Dus'];

    /**
     * Menghitung total tarif pengiriman kargo reguler.
     * Rumus: Berat (kg) × Tarif Dasar per Kg
     *
     * @return float
     */
    protected function hitungTarifPengiriman(): float
    {
        return (float) $this->beratBarang * (float) $this->tarifDasarPerKg;
    }

    /**
     * Rincian komponen tarif untuk ditampilkan di dashboard.
     *
     * @return array{tarif_berat: float, formula: string}
     */
    public function getRincianPerhitungan(): array
    {
        $tarifBerat = (float) $this->beratBarang * (float) $this->tarifDasarPerKg;

        return [
            'tarif_berat' => $tarifBerat,
            'formula' => sprintf(
                '%s kg × Rp %s',
                $this->beratBarang,
                number_format($this->tarifDasarPerKg, 0, ',', '.')
            ),
        ];
    }

    /**
     * Validasi SOP packing untuk kargo reguler.
     * Aturan:
     * 1. Berat barang harus lebih dari 0 kg
     * 2. Jenis paket harus 'Koli' atau 'Dus'
     *
     * @return bool
     */
    protected function validasiSOPPacking(): bool
    {
        // Aturan 1: berat harus positif
        if ($this->beratBarang <= 0) {
            return false;
        }

        // Aturan 2: jenis paket harus sesuai ENUM di tabel kargo_reguler
        if (!in_array($this->jenisPaket, self::JENIS_PAKET_VALID, true)) {
            return false;
        }

        return true;
    }

    /**
     * Simpan data spesifik kargo reguler ke tabel `kargo_reguler`.
     * Pastikan data induk sudah tersimpan di tabel `kargo` terlebih dahulu
     * agar foreign key id_resi terpenuhi.
     *
     * @return bool
     */
    public function simpanKargoReguler(?PDO $pdo = null): bool
    {
        // Generate ID resi dengan prefix REG jika belum ada
        if (empty($this->id_resi)) {
            $this->generateIdResi(self::PREFIX_ID_RESI);
        }

        try {
            if ($pdo === null) {
                require __DIR__ . '/../config/connection.php';
            }

            $sql = "INSERT INTO kargo_reguler
                    (id_resi, jenis_paket, estimasi_hari)
                    VALUES
                    (:id_resi, :jenis_paket, :estimasi_hari)";

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_resi', $this->id_resi);
            $stmt->bindValue(':jenis_paket', $this->jenisPaket);
            $stmt->bindValue(':estimasi_hari', $this->estimasiHari, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('KargoReguler::simpanKargoReguler error: ' . $e->getMessage());
            return false;
        }
    }

    // --- Setter & Getter atribut tambahan KargoReguler ---

    public function setJenisPaket(string $jenisPaket): void
    {
        $this->jenisPaket = $jenisPaket;
    }

    public function getJenisPaket(): ?string
    {
        return $this->jenisPaket;
    }

    public function setEstimasiHari(int $estimasiHari): void
    {
        $this->estimasiHari = $estimasiHari;
    }

    public function getEstimasiHari(): ?int
    {
        return $this->estimasiHari;
    }
}
