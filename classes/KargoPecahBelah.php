<?php

require_once __DIR__ . '/Kargo.php';

/**
 * Class KargoPecahBelah
 *
 * Subclass dari abstract class Kargo untuk pengiriman cargo yang mudah pecah/belah.
 * Jenis kargo ini memerlukan perlindungan ekstra dengan bubble wrap dan asuransi wajib.
 */
class KargoPecahBelah extends Kargo
{
    /** @var string Ketebalan lapisan bubble wrap (contoh: "3 lapis", "5 lapis") */
    protected $ketebalanBubbleWrap;

    /** @var float Biaya asuransi wajib untuk kargo pecah/belah */
    protected $biayaAsuransiWajib;

    /** @var string Prefix ID resi khusus kargo pecah/belah */
    private const PREFIX_ID_RESI = 'PB';

    /** @var int Minimum ketebalan bubble wrap yang diperbolehkan (lapis) */
    private const MIN_KETEBALAN_BUBBLE_WRAP = 2;

    /** @var float Minimum biaya asuransi wajib */
    private const MIN_BIAYA_ASURANSI = 5000;

    /** @var float Persentase surcharge fragile dari tarif berat */
    private const SURCHARGE_FRAGILE_PERSEN = 0.05;

    /**
     * Constructor untuk KargoPecahBelah.
     * Menginisialisasi atribut dari parent class dan atribut tambahan.
     *
     * @param string|null $pengirim Nama pengirim
     * @param string|null $kotaTujuan Kota tujuan pengiriman
     * @param float|null $beratBarang Berat barang dalam kg
     * @param float|null $tarifDasarPerKg Tarif dasar per kg
     * @param string|null $ketebalanBubbleWrap Ketebalan bubble wrap (misal: "3 lapis")
     * @param float|null $biayaAsuransiWajib Biaya asuransi wajib
     */
    public function __construct(
        ?string $pengirim = null,
        ?string $kotaTujuan = null,
        ?float $beratBarang = null,
        ?float $tarifDasarPerKg = null,
        ?string $ketebalanBubbleWrap = null,
        ?float $biayaAsuransiWajib = null
    ) {
        $this->pengirim = $pengirim;
        $this->kotaTujuan = $kotaTujuan;
        $this->beratBarang = $beratBarang;
        $this->tarifDasarPerKg = $tarifDasarPerKg;
        $this->ketebalanBubbleWrap = $ketebalanBubbleWrap;
        $this->biayaAsuransiWajib = 20000.0; // DIPATOK TETAP Rp 20.000 oleh sistem
    }

    /**
     * Menghitung total tarif pengiriman kargo pecah/belah.
     * Rumus: (Berat × Tarif Dasar per Kg) + Biaya Asuransi + Surcharge Fragile (5% tarif berat)
     *
     * @return float
     */
    protected function hitungTarifPengiriman(): float
    {
        $tarifBerat = (float) $this->beratBarang * (float) $this->tarifDasarPerKg;
        $surchargeFragile = $tarifBerat * self::SURCHARGE_FRAGILE_PERSEN;
        $biayaAsuransi = (float) ($this->biayaAsuransiWajib ?? 0);

        return $tarifBerat + $biayaAsuransi + $surchargeFragile;
    }

    /**
     * Rincian komponen tarif untuk ditampilkan di dashboard.
     *
     * @return array{tarif_berat: float, surcharge_fragile: float, biaya_asuransi: float, formula: string}
     */
    public function getRincianPerhitungan(): array
    {
        $tarifBerat = (float) $this->beratBarang * (float) $this->tarifDasarPerKg;
        $surchargeFragile = $tarifBerat * self::SURCHARGE_FRAGILE_PERSEN;
        $biayaAsuransi = (float) ($this->biayaAsuransiWajib ?? 0);

        return [
            'tarif_berat' => $tarifBerat,
            'surcharge_fragile' => $surchargeFragile,
            'biaya_asuransi' => $biayaAsuransi,
            'formula' => sprintf(
                '(%s kg × Rp %s) + Rp %s (asuransi) + Rp %s (surcharge fragile 5%%)',
                $this->beratBarang,
                number_format($this->tarifDasarPerKg, 0, ',', '.'),
                number_format($biayaAsuransi, 0, ',', '.'),
                number_format($surchargeFragile, 0, ',', '.')
            ),
        ];
    }

    /**
     * Validasi SOP packing untuk kargo pecah/belah.
     * Aturan:
     * 1. Berat barang harus lebih dari 0 kg
     * 2. Ketebalan bubble wrap harus minimal 2 lapis
     * 3. Biaya asuransi wajib minimal Rp 5.000
     *
     * @return bool
     */
    protected function validasiSOPPacking(): bool
    {
        // Aturan 1: berat harus positif
        if ($this->beratBarang <= 0) {
            return false;
        }

        // Aturan 2: validasi ketebalan bubble wrap
        if (!$this->validasiKetebalanBubbleWrap()) {
            return false;
        }

        // Aturan 3: biaya asuransi harus minimal Rp 5.000
        if (($this->biayaAsuransiWajib ?? 0) < self::MIN_BIAYA_ASURANSI) {
            return false;
        }

        return true;
    }

    /**
     * Validasi ketebalan bubble wrap.
     * Ekstrak angka dari string (misal: "3 lapis" → 3) dan periksa minimal ketebalan.
     *
     * @return bool
     */
    private function validasiKetebalanBubbleWrap(): bool
    {
        if (empty($this->ketebalanBubbleWrap)) {
            return false;
        }

        // Ekstrak angka dari string ketebalan (misal: "3 lapis" → 3)
        preg_match('/\d+/', $this->ketebalanBubbleWrap, $matches);
        $angkaKetebalan = isset($matches[0]) ? (int) $matches[0] : 0;

        return $angkaKetebalan >= self::MIN_KETEBALAN_BUBBLE_WRAP;
    }

    /**
     * Simpan data spesifik kargo pecah/belah ke tabel `kargo_pecah_belah`.
     * Pastikan data induk sudah tersimpan di tabel `kargo` terlebih dahulu
     * agar foreign key id_resi terpenuhi.
     *
     * @return bool
     */
    public function simpanKargoPecahBelah(?PDO $pdo = null): bool
    {
        // Generate ID resi dengan prefix PB jika belum ada
        if (empty($this->id_resi)) {
            $this->generateIdResi(self::PREFIX_ID_RESI);
        }

        try {
            if ($pdo === null) {
                require __DIR__ . '/../config/connection.php';
            }

            $sql = "INSERT INTO kargo_pecah_belah
                    (id_resi, ketebalan_bubble_wrap, biaya_asuransi_wajib)
                    VALUES
                    (:id_resi, :ketebalan_bubble_wrap, :biaya_asuransi_wajib)";

            // Ekstrak numeric part dari ketebalan bubble wrap (misal: "3 lapis" -> 3.00)
            preg_match('/\d+/', $this->ketebalanBubbleWrap ?? '0', $matches);
            $angkaKetebalan = isset($matches[0]) ? (float) $matches[0] : 0.0;

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_resi', $this->id_resi);
            $stmt->bindValue(':ketebalan_bubble_wrap', $angkaKetebalan);
            $stmt->bindValue(':biaya_asuransi_wajib', $this->biayaAsuransiWajib, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('KargoPecahBelah::simpanKargoPecahBelah error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Dapatkan ringkasan tarif untuk kargo pecah/belah.
     *
     * @return array
     */
    public function getRingkasanTarif(): array
    {
        $rincian = $this->getRincianPerhitungan();
        $totalTarif = $this->getTotalTarif();

        return [
            'tarif_dasar' => $rincian['tarif_berat'],
            'surcharge_fragile' => $rincian['surcharge_fragile'],
            'biaya_asuransi_wajib' => $rincian['biaya_asuransi'],
            'total_tarif' => $totalTarif,
            'detail' => sprintf(
                'Tarif Berat: Rp %s | Surcharge Fragile: Rp %s | Asuransi: Rp %s | Total: Rp %s',
                number_format($rincian['tarif_berat'], 0, ',', '.'),
                number_format($rincian['surcharge_fragile'], 0, ',', '.'),
                number_format($rincian['biaya_asuransi'], 0, ',', '.'),
                number_format($totalTarif, 0, ',', '.')
            ),
        ];
    }

    // --- Setter & Getter atribut tambahan KargoPecahBelah ---

    /**
     * Set ketebalan bubble wrap.
     *
     * @param string $ketebalanBubbleWrap Ketebalan bubble wrap (misal: "3 lapis")
     * @return void
     */
    public function setKetebalanBubbleWrap(string $ketebalanBubbleWrap): void
    {
        $this->ketebalanBubbleWrap = $ketebalanBubbleWrap;
    }

    /**
     * Get ketebalan bubble wrap.
     *
     * @return string|null
     */
    public function getKetebalanBubbleWrap(): ?string
    {
        return $this->ketebalanBubbleWrap;
    }

    /**
     * Set biaya asuransi wajib.
     *
     * @param float $biayaAsuransiWajib Biaya asuransi wajib
     * @return void
     */
    public function setBiayaAsuransiWajib(float $biayaAsuransiWajib): void
    {
        $this->biayaAsuransiWajib = $biayaAsuransiWajib;
    }

    /**
     * Get biaya asuransi wajib.
     *
     * @return float|null
     */
    public function getBiayaAsuransiWajib(): ?float
    {
        return $this->biayaAsuransiWajib;
    }
}
