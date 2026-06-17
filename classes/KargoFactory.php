<?php

require_once __DIR__ . '/Kargo.php';
require_once __DIR__ . '/KargoReguler.php';
require_once __DIR__ . '/KargoBahanKimia.php';
require_once __DIR__ . '/KargoPecahBelah.php';

/**
 * Factory untuk instansiasi object Kargo berdasarkan jenis yang dipilih.
 * Memusatkan tarif dasar di backend dan menerapkan polimorfisme.
 */
class KargoFactory
{
    /** @var array<string, mixed> */
    private static ?array $tarifConfig = null;

    /**
     * Ambil tarif dasar per kg dari config (hardcode / DB nantinya).
     */
    public static function getTarifDasarPerKg()
    {
        // Langsung tetapkan harga di sini, tidak perlu require file config lain
        $tarifDasar = 10000;
        return $tarifDasar;
    }

    /**
     * Buat instance Kargo dari data form POST.
     *
     * @param array<string, mixed> $post
     * @return Kargo|null Null jika jenis kargo tidak valid
     */
    public static function createFromPost(array $post): ?Kargo
    {
        $jenisKargo = $post['jenis_kargo'] ?? '';
        $berat = floatval($post['berat'] ?? 0);
        $tarifDasar = self::getTarifDasarPerKg();

        if (!$jenisKargo || $berat <= 0) {
            return null;
        }

        switch ($jenisKargo) {
            case 'reguler':
                $kargo = new KargoReguler();
                $kargo->setPengirim(trim($post['pengirim'] ?? ''));
                $kargo->setKotaTujuan(trim($post['kota_tujuan'] ?? ''));
                $kargo->setBeratBarang($berat);
                $kargo->setTarifDasarPerKg($tarifDasar);
                $kargo->setJenisPaket($post['jenis_paket'] ?? 'Koli');
                $kargo->setEstimasiHari(intval($post['estimasi_hari'] ?? 1));
                return $kargo;

            case 'kimia':
                $tingkatBahaya = intval($post['tingkat_bahaya'] ?? 1);
                $kargo = new KargoBahanKimia(
                    trim($post['pengirim'] ?? ''),
                    trim($post['kota_tujuan'] ?? ''),
                    $berat,
                    $tarifDasar,
                    $tingkatBahaya,
                    trim($post['jenis_sertifikasi'] ?? 'AUTO-RESERVASI')
                );
                return $kargo;

            case 'pecah_belah':
                $kargo = new KargoPecahBelah(
                    trim($post['pengirim'] ?? ''),
                    trim($post['kota_tujuan'] ?? ''),
                    $berat,
                    $tarifDasar,
                    $post['ketebalan_bubble_wrap'] ?? '3 lapis',
                    20000.0 // Force Rp 20.000 insurance wajib
                );
                return $kargo;

            default:
                return null;
        }
    }

    /**
     * Buat instance Kargo untuk kalkulator tarif (tanpa wajib pengirim/kota).
     *
     * @param array<string, mixed> $post
     * @return Kargo|null
     */
    public static function createForKalkulator(array $post): ?Kargo
    {
        $post['pengirim'] = $post['pengirim'] ?? '-';
        $post['kota_tujuan'] = $post['kota_tujuan'] ?? '-';

        return self::createFromPost($post);
    }

    /**
     * Label jenis kargo untuk tampilan UI.
     */
    public static function getJenisLabel(Kargo $kargo): string
    {
        if ($kargo instanceof KargoReguler) {
            return 'Kargo Reguler';
        }
        if ($kargo instanceof KargoBahanKimia) {
            return 'Kargo Bahan Kimia';
        }
        if ($kargo instanceof KargoPecahBelah) {
            return 'Kargo Pecah Belah';
        }

        return 'Kargo';
    }

    /**
     * Prefix ID resi sesuai subclass.
     */
    public static function getPrefixResi(Kargo $kargo): string
    {
        if ($kargo instanceof KargoReguler) {
            return 'REG';
        }
        if ($kargo instanceof KargoBahanKimia) {
            return 'KIM';
        }
        if ($kargo instanceof KargoPecahBelah) {
            return 'PB';
        }

        return 'KGO';
    }

    /**
     * Nama jenis kargo untuk disimpan ke tabel kargo.
     */
    public static function getJenisKargoDb(Kargo $kargo): string
    {
        if ($kargo instanceof KargoReguler) {
            return 'Reguler';
        }
        if ($kargo instanceof KargoBahanKimia) {
            return 'BahanKimia';
        }
        if ($kargo instanceof KargoPecahBelah) {
            return 'PecahBelah';
        }

        return 'Reguler';
    }

    /**
     * Susun hasil perhitungan tarif menggunakan polimorfisme.
     *
     * @return array<string, mixed>
     */
    public static function buildHasilPerhitungan(Kargo $kargo): array
    {
        $rincian = $kargo->getRincianPerhitungan();

        return [
            'jenis' => self::getJenisLabel($kargo),
            'berat' => $kargo->getBeratBarang(),
            'tarif_dasar' => $kargo->getTarifDasarPerKg(),
            'formula' => $rincian['formula'],
            'tarif_total' => $kargo->getTotalTarif(),
            'status_packing' => $kargo->cekValidasiSOPPacking() ? 'TERPENUHI' : 'BELUM TERPENUHI',
        ];
    }

    /**
     * Susun hasil reservasi menggunakan polimorfisme.
     *
     * @return array<string, mixed>
     */
    public static function buildHasilReservasi(Kargo $kargo): array
    {
        $prefix = self::getPrefixResi($kargo);
        $kargo->generateIdResi($prefix);

        return [
            'jenis' => self::getJenisLabel($kargo),
            'resi' => $kargo->getIdResi(),
            'tarif' => $kargo->getTotalTarif(),
            'status_packing' => $kargo->cekValidasiSOPPacking() ? 'TERPENUHI' : 'BELUM TERPENUHI',
            'formula' => $kargo->getRincianPerhitungan()['formula'],
        ];
    }
}
