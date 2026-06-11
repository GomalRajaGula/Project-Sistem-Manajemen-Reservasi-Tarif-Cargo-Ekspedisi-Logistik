<?php
require_once __DIR__ . '/../classes/Kargo.php';
require_once __DIR__ . '/../classes/KargoReguler.php';
require_once __DIR__ . '/../classes/KargoBahanKimia.php';
require_once __DIR__ . '/../classes/KargoPecahBelah.php';

$message = '';
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis_kargo = $_POST['jenis_kargo'] ?? '';
    $pengirim = $_POST['pengirim'] ?? '';
    $kota_tujuan = $_POST['kota_tujuan'] ?? '';
    $berat = floatval($_POST['berat'] ?? 0);
    $tarif_dasar = floatval($_POST['tarif_dasar'] ?? 0);

    if ($jenis_kargo && $pengirim && $kota_tujuan && $berat && $tarif_dasar) {
        switch ($jenis_kargo) {
            case 'reguler':
                $jenis_paket = $_POST['jenis_paket'] ?? 'Koli';
                $estimasi = intval($_POST['estimasi_hari'] ?? 1);
                $tarif = $berat * $tarif_dasar;
                $message = "Reservasi Kargo Reguler berhasil dicatat!";
                $result = [
                    'jenis' => 'Reguler',
                    'resi' => 'REG' . date('YmdHis'),
                    'tarif' => $tarif
                ];
                break;
            case 'kimia':
                $tingkat_bahaya = intval($_POST['tingkat_bahaya'] ?? 1);
                $mult = ($tingkat_bahaya <= 3) ? 1.2 : (($tingkat_bahaya <= 6) ? 1.5 : 2.0);
                $tarif = $berat * $tarif_dasar * $mult;
                $message = "Reservasi Kargo Bahan Kimia berhasil dicatat!";
                $result = [
                    'jenis' => 'Bahan Kimia',
                    'resi' => 'KIM' . date('YmdHis'),
                    'tarif' => $tarif
                ];
                break;
            case 'pecah_belah':
                $asuransi = floatval($_POST['asuransi'] ?? 50000);
                $tarif = ($berat * $tarif_dasar) + $asuransi;
                $message = "Reservasi Kargo Pecah Belah berhasil dicatat!";
                $result = [
                    'jenis' => 'Pecah Belah',
                    'resi' => 'PB' . date('YmdHis'),
                    'tarif' => $tarif
                ];
                break;
        }
    } else {
        $message = "Mohon lengkapi semua data yang diperlukan!";
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
    <title>Reservasi Pengiriman - Cargo Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Reservasi Pengiriman</h1>
                <p class="page-subtitle">Buat reservasi pengiriman cargo baru</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                <!-- Form -->
                <div class="card">
                    <h3 class="card-title">Form Reservasi</h3>
                    
                    <?php if ($message): ?>
                        <div style="background: rgba(16,185,129,0.1); border: 1px solid #10b981; color: #10b981; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
                            ✓ <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Jenis Kargo *</label>
                            <select name="jenis_kargo" class="form-select" required onchange="updateFields()">
                                <option value="">-- Pilih Jenis Kargo --</option>
                                <option value="reguler">Kargo Reguler</option>
                                <option value="kimia">Kargo Bahan Kimia</option>
                                <option value="pecah_belah">Kargo Pecah Belah</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nama Pengirim *</label>
                            <input type="text" name="pengirim" class="form-input" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Kota Tujuan *</label>
                            <input type="text" name="kota_tujuan" class="form-input" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Berat (kg) *</label>
                                <input type="number" name="berat" class="form-input" step="0.1" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tarif Dasar/kg *</label>
                                <input type="number" name="tarif_dasar" class="form-input" required>
                            </div>
                        </div>

                        <!-- Fields untuk Reguler -->
                        <div id="fields-reguler" style="display: none;">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Jenis Paket</label>
                                    <select name="jenis_paket" class="form-select">
                                        <option>Koli</option>
                                        <option>Dus</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Estimasi Hari</label>
                                    <input type="number" name="estimasi_hari" class="form-input" value="1">
                                </div>
                            </div>
                        </div>

                        <!-- Fields untuk Kimia -->
                        <div id="fields-kimia" style="display: none;">
                            <div class="form-group">
                                <label class="form-label">Tingkat Bahaya (1-9)</label>
                                <input type="number" name="tingkat_bahaya" class="form-input" min="1" max="9" value="1">
                            </div>
                        </div>

                        <!-- Fields untuk Pecah Belah -->
                        <div id="fields-pecah-belah" style="display: none;">
                            <div class="form-group">
                                <label class="form-label">Biaya Asuransi (Rp)</label>
                                <input type="number" name="asuransi" class="form-input" value="50000">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">Buat Reservasi</button>
                    </form>
                </div>

                <!-- Result -->
                <div class="card">
                    <h3 class="card-title">Hasil Reservasi</h3>
                    
                    <?php if ($result): ?>
                        <div style="background: var(--border-light); padding: 20px; border-radius: 8px;">
                            <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
                                <div style="font-size: 12px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 4px;">Jenis Kargo</div>
                                <div style="font-size: 16px; font-weight: 600; color: var(--primary-color);"><?php echo $result['jenis']; ?></div>
                            </div>

                            <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
                                <div style="font-size: 12px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 4px;">No. Resi</div>
                                <div style="font-size: 14px; font-weight: 600; font-family: 'Courier New', monospace; color: var(--accent-color);"><?php echo $result['resi']; ?></div>
                            </div>

                            <div>
                                <div style="font-size: 12px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 4px;">Tarif Pengiriman</div>
                                <div style="font-size: 24px; font-weight: 700; color: var(--success);"><?php echo formatRupiah($result['tarif']); ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-light" style="text-align: center; padding: 40px 20px;">Silakan isi form dan klik "Buat Reservasi" untuk melihat hasil di sini.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        function updateFields() {
            const jenis = document.querySelector('select[name="jenis_kargo"]').value;
            document.getElementById('fields-reguler').style.display = jenis === 'reguler' ? 'block' : 'none';
            document.getElementById('fields-kimia').style.display = jenis === 'kimia' ? 'block' : 'none';
            document.getElementById('fields-pecah-belah').style.display = jenis === 'pecah_belah' ? 'block' : 'none';
        }
    </script>
</body>
</html>
