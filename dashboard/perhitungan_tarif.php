<?php
require_once __DIR__ . '/../classes/KargoFactory.php';

$result = null;
$tarifDasarPerKg = KargoFactory::getTarifDasarPerKg();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis_kargo = $_POST['jenis_kargo'] ?? '';
    $berat = floatval($_POST['berat'] ?? 0);

    if ($jenis_kargo && $berat > 0) {
        $kargo = KargoFactory::createForKalkulator($_POST);

        if ($kargo !== null) {
            // Polimorfisme: tarif dihitung oleh subclass masing-masing
            $result = KargoFactory::buildHasilPerhitungan($kargo);
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
            <div class="page-header">
                <h1 class="page-title">Perhitungan Tarif</h1>
                <p class="page-subtitle">Kalkulator tarif pengiriman berdasarkan jenis kargo (OOP Polimorfisme)</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                <div class="card">
                    <h3 class="card-title">Hitung Tarif Pengiriman</h3>

                    <div style="background: rgba(59,130,246,0.08); border: 1px solid #3b82f6; color: #1d4ed8; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
                        Tarif dasar/kg: <strong><?php echo formatRupiah($tarifDasarPerKg); ?></strong> (ditetapkan sistem)
                    </div>

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

                        <div class="form-group">
                            <label class="form-label">Berat Barang (kg) *</label>
                            <input type="number" name="berat" class="form-input" step="0.1" value="<?php echo htmlspecialchars($_POST['berat'] ?? ''); ?>" required>
                        </div>

                        <div id="form-kimia" style="display: <?php echo ($_POST['jenis_kargo'] ?? '') === 'kimia' ? 'block' : 'none'; ?>;">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Tingkat Bahaya (Class 1-9)</label>
                                    <input type="number" name="tingkat_bahaya" class="form-input" min="1" max="9" value="<?php echo htmlspecialchars($_POST['tingkat_bahaya'] ?? '5'); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Sertifikasi Keamanan</label>
                                    <input type="text" name="jenis_sertifikasi" class="form-input" value="<?php echo htmlspecialchars($_POST['jenis_sertifikasi'] ?? 'MSDS-SND'); ?>">
                                </div>
                            </div>
                            <small class="text-light" style="display: block; margin-top: 8px;">
                                Rumus: (Berat × Tarif Dasar) + (Class × Rp 100.000)
                            </small>
                        </div>

                        <div id="form-pecah-belah" style="display: <?php echo ($_POST['jenis_kargo'] ?? '') === 'pecah_belah' ? 'block' : 'none'; ?>;">
                            <div class="form-group">
                                <label class="form-label">Ketebalan Bubble Wrap</label>
                                <input type="text" name="ketebalan_bubble_wrap" class="form-input" value="<?php echo htmlspecialchars($_POST['ketebalan_bubble_wrap'] ?? '3 lapis'); ?>">
                            </div>
                            <small class="text-light" style="display: block; margin-bottom: 16px;">
                                Rumus: (Berat × Tarif Dasar) + Rp 20.000 (Asuransi) + Surcharge Fragile 5%
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">Hitung Tarif</button>
                    </form>
                </div>

                <div class="card">
                    <h3 class="card-title">Hasil Perhitungan</h3>

                    <?php if ($result): ?>
                        <div style="background: var(--border-light); padding: 24px; border-radius: 8px;">
                            <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                                <div style="font-size: 12px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Jenis Kargo</div>
                                <div style="font-size: 18px; font-weight: 600; color: var(--primary-color);"><?php echo htmlspecialchars($result['jenis']); ?></div>
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
                                    <tr>
                                        <td style="padding: 6px 0; color: var(--text-secondary);">Status SOP</td>
                                        <td style="padding: 6px 0; text-align: right; color: <?php echo $result['status_packing'] === 'TERPENUHI' ? 'var(--success)' : '#ef4444'; ?>; font-weight: 500;">
                                            <?php echo htmlspecialchars($result['status_packing']); ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                                <div style="font-size: 12px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;">Formula (via Polimorfisme)</div>
                                <div style="font-size: 12px; color: var(--text-primary); background: var(--white); padding: 12px; border-radius: 6px; font-family: 'Courier New', monospace;">
                                    <?php echo htmlspecialchars($result['formula']); ?>
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
