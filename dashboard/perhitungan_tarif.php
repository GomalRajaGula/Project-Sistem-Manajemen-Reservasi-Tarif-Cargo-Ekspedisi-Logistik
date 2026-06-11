<?php
require_once __DIR__ . '/../classes/Kargo.php';
require_once __DIR__ . '/../classes/KargoReguler.php';
require_once __DIR__ . '/../classes/KargoBahanKimia.php';
require_once __DIR__ . '/../classes/KargoPecahBelah.php';

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis_kargo = $_POST['jenis_kargo'] ?? '';
    $berat = floatval($_POST['berat'] ?? 0);
    $tarif_dasar = floatval($_POST['tarif_dasar'] ?? 0);

    if ($jenis_kargo && $berat && $tarif_dasar) {
        switch ($jenis_kargo) {
            case 'reguler':
                $tarif = $berat * $tarif_dasar;
                $result = [
                    'jenis' => 'Kargo Reguler',
                    'berat' => $berat,
                    'tarif_dasar' => $tarif_dasar,
                    'formula' => "$berat kg × Rp " . number_format($tarif_dasar, 0, ',', '.'),
                    'tarif_total' => $tarif
                ];
                break;
            case 'kimia':
                $tingkat_bahaya = intval($_POST['tingkat_bahaya'] ?? 1);
                if ($tingkat_bahaya <= 3) {
                    $mult = 1.2;
                    $kategori = 'Rendah';
                } elseif ($tingkat_bahaya <= 6) {
                    $mult = 1.5;
                    $kategori = 'Sedang';
                } else {
                    $mult = 2.0;
                    $kategori = 'Tinggi';
                }
                $tarif = $berat * $tarif_dasar * $mult;
                $result = [
                    'jenis' => 'Kargo Bahan Kimia',
                    'berat' => $berat,
                    'tarif_dasar' => $tarif_dasar,
                    'formula' => "$berat kg × Rp " . number_format($tarif_dasar, 0, ',', '.') . " × $mult (Class $tingkat_bahaya - $kategori)",
                    'tarif_total' => $tarif
                ];
                break;
            case 'pecah_belah':
                $asuransi = floatval($_POST['asuransi'] ?? 50000);
                $tarif_jenis = $berat * $tarif_dasar;
                $tarif = $tarif_jenis + $asuransi;
                $result = [
                    'jenis' => 'Kargo Pecah Belah',
                    'berat' => $berat,
                    'tarif_dasar' => $tarif_dasar,
                    'formula' => "($berat kg × Rp " . number_format($tarif_dasar, 0, ',', '.') . ") + Rp " . number_format($asuransi, 0, ',', '.'),
                    'tarif_total' => $tarif
                ];
                break;
        }
    }
}

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perhitungan Tarif - Cargo Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Perhitungan Tarif</h1>
                <p class="page-subtitle">Kalkulator tarif pengiriman berdasarkan jenis kargo</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                <!-- Calculator Form -->
                <div class="card">
                    <h3 class="card-title">Hitung Tarif Pengiriman</h3>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Pilih Jenis Kargo *</label>
                            <select name="jenis_kargo" class="form-select" required onchange="updateForm()">
                                <option value="">-- Pilih Jenis Kargo --</option>
                                <option value="reguler" <?php echo ($_POST['jenis_kargo'] ?? '') === 'reguler' ? 'selected' : ''; ?>>Kargo Reguler</option>
                                <option value="kimia" <?php echo ($_POST['jenis_kargo'] ?? '') === 'kimia' ? 'selected' : ''; ?>>Kargo Bahan Kimia</option>
                                <option value="pecah_belah" <?php echo ($_POST['jenis_kargo'] ?? '') === 'pecah_belah' ? 'selected' : ''; ?>>Kargo Pecah Belah</option>
                            </select>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Berat Barang (kg) *</label>
                                <input type="number" name="berat" class="form-input" step="0.1" value="<?php echo $_POST['berat'] ?? ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tarif Dasar/kg (Rp) *</label>
                                <input type="number" name="tarif_dasar" class="form-input" value="<?php echo $_POST['tarif_dasar'] ?? ''; ?>" required>
                            </div>
                        </div>

                        <!-- Tambahan untuk Kimia -->
                        <div id="form-kimia" style="display: <?php echo ($_POST['jenis_kargo'] ?? '') === 'kimia' ? 'block' : 'none'; ?>;">
                            <div class="form-group">
                                <label class="form-label">Tingkat Bahaya (1-9)</label>
                                <input type="number" name="tingkat_bahaya" class="form-input" min="1" max="9" value="<?php echo $_POST['tingkat_bahaya'] ?? '5'; ?>">
                                <small class="text-light" style="display: block; margin-top: 8px;">• Class 1-3 (Rendah): Multiplier 1.2x<br>• Class 4-6 (Sedang): Multiplier 1.5x<br>• Class 7-9 (Tinggi): Multiplier 2.0x</small>
                            </div>
                        </div>

                        <!-- Tambahan untuk Pecah Belah -->
                        <div id="form-pecah-belah" style="display: <?php echo ($_POST['jenis_kargo'] ?? '') === 'pecah_belah' ? 'block' : 'none'; ?>;">
                            <div class="form-group">
                                <label class="form-label">Biaya Asuransi Wajib (Rp)</label>
                                <input type="number" name="asuransi" class="form-input" value="<?php echo $_POST['asuransi'] ?? '50000'; ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">Hitung Tarif</button>
                    </form>
                </div>

                <!-- Result -->
                <div class="card">
                    <h3 class="card-title">Hasil Perhitungan</h3>
                    
                    <?php if ($result): ?>
                        <div style="background: var(--border-light); padding: 24px; border-radius: 8px;">
                            <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                                <div style="font-size: 12px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Jenis Kargo</div>
                                <div style="font-size: 18px; font-weight: 600; color: var(--primary-color);"><?php echo $result['jenis']; ?></div>
                            </div>

                            <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                                <div style="font-size: 12px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Detail Perhitungan</div>
                                <table style="width: 100%; font-size: 13px;">
                                    <tr>
                                        <td style="padding: 6px 0; color: var(--text-secondary);">Berat</td>
                                        <td style="padding: 6px 0; text-align: right; color: var(--text-primary); font-weight: 500;"><?php echo $result['berat']; ?> kg</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 6px 0; color: var(--text-secondary);">Tarif Dasar/kg</td>
                                        <td style="padding: 6px 0; text-align: right; color: var(--text-primary); font-weight: 500;"><?php echo formatRupiah($result['tarif_dasar']); ?></td>
                                    </tr>
                                </table>
                            </div>

                            <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                                <div style="font-size: 12px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Formula Perhitungan</div>
                                <div style="font-size: 12px; color: var(--text-primary); background: var(--white); padding: 12px; border-radius: 6px; font-family: 'Courier New', monospace;">
                                    <?php echo $result['formula']; ?>
                                </div>
                            </div>

                            <div>
                                <div style="font-size: 12px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Total Tarif</div>
                                <div style="font-size: 32px; font-weight: 700; color: var(--success);"><?php echo formatRupiah($result['tarif_total']); ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-light" style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">Belum ada perhitungan.<br>Silakan isi form dan klik "Hitung Tarif".</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        function updateForm() {
            const jenis = document.querySelector('select[name="jenis_kargo"]').value;
            document.getElementById('form-kimia').style.display = jenis === 'kimia' ? 'block' : 'none';
            document.getElementById('form-pecah-belah').style.display = jenis === 'pecah_belah' ? 'block' : 'none';
        }
    </script>
</body>
</html>
