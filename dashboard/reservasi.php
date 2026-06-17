<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../classes/KargoFactory.php';

$message = $_SESSION['success_message'] ?? '';
$error = false;
if (isset($_SESSION['success_message'])) {
    unset($_SESSION['success_message']);
}

$result = null;
$tarifDasarPerKg = KargoFactory::getTarifDasarPerKg();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis_kargo = $_POST['jenis_kargo'] ?? '';
    $pengirim = trim($_POST['pengirim'] ?? '');
    $kota_tujuan = trim($_POST['kota_tujuan'] ?? '');
    $berat = floatval($_POST['berat'] ?? 0);

    if ($jenis_kargo && $pengirim && $kota_tujuan && $berat > 0) {
        $kargo = KargoFactory::createFromPost($_POST);

        if ($kargo !== null) {
            // Generate Resi so it's populated for both inserts
            $prefix = KargoFactory::getPrefixResi($kargo);
            $kargo->generateIdResi($prefix);
            
            // Transactional database insertion
            try {
                require_once __DIR__ . '/../config/connection.php';
                $pdo->beginTransaction();
                
                // Save Parent
                $jenisDb = KargoFactory::getJenisKargoDb($kargo);
                $parentSaved = $kargo->simpanKargo($jenisDb, $pdo);
                
                // Save Child
                $childSaved = false;
                if ($parentSaved) {
                    if ($kargo instanceof KargoReguler) {
                        $childSaved = $kargo->simpanKargoReguler($pdo);
                    } elseif ($kargo instanceof KargoBahanKimia) {
                        $childSaved = $kargo->simpanKargoBahanKimia($pdo);
                    } elseif ($kargo instanceof KargoPecahBelah) {
                        $childSaved = $kargo->simpanKargoPecahBelah($pdo);
                    }
                }
                
                if ($parentSaved && $childSaved) {
                    $pdo->commit();
                    
                    // Set session success message
                    $_SESSION['success_message'] = 'Reservasi ' . KargoFactory::getJenisLabel($kargo) . ' dengan No. Resi ' . $kargo->getIdResi() . ' berhasil disimpan!';
                    
                    // Redirect based on type
                    $redirectPage = 'kargo_reguler.php';
                    if ($kargo instanceof KargoBahanKimia) {
                        $redirectPage = 'kargo_kimia.php';
                    } elseif ($kargo instanceof KargoPecahBelah) {
                        $redirectPage = 'kargo_pecah_belah.php';
                    }
                    
                    $baseUrl = 'http://localhost/Project-Sistem-Manajemen-Reservasi-Tarif-Cargo-Ekspedisi-Logistik/dashboard/';
                    header('Location: ' . $baseUrl . $redirectPage);
                    exit;
                } else {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $message = 'Gagal menyimpan reservasi ke database!';
                    $error = true;
                }
            } catch (Exception $e) {
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $message = 'Terjadi error: ' . $e->getMessage();
                $error = true;
            }
        } else {
            $message = 'Jenis kargo tidak valid!';
            $error = true;
        }
    } else {
        $message = 'Mohon lengkapi semua data yang diperlukan!';
        $error = true;
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
            <div class="page-header">
                <h1 class="page-title">Reservasi Pengiriman</h1>
                <p class="page-subtitle">Buat reservasi pengiriman cargo baru</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                <div class="card">
                    <h3 class="card-title">Form Reservasi</h3>

                    <div style="background: rgba(59,130,246,0.08); border: 1px solid #3b82f6; color: #1d4ed8; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
                        Tarif dasar/kg: <strong><?php echo formatRupiah($tarifDasarPerKg); ?></strong> (ditetapkan sistem)
                    </div>

                    <?php if ($message): ?>
                        <div style="background: <?php echo $error ? 'rgba(239,68,68,0.1)' : 'rgba(16,185,129,0.1)'; ?>; border: 1px solid <?php echo $error ? '#ef4444' : '#10b981'; ?>; color: <?php echo $error ? '#ef4444' : '#10b981'; ?>; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 13px;">
                            <?php echo $error ? '✗' : '✓'; ?> <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Jenis Kargo *</label>
                            <select name="jenis_kargo" class="form-select" required onchange="updateFields()">
                                <option value="">-- Pilih Jenis Kargo --</option>
                                <option value="reguler" <?php echo ($_POST['jenis_kargo'] ?? '') === 'reguler' ? 'selected' : ''; ?>>Kargo Reguler</option>
                                <option value="kimia" <?php echo ($_POST['jenis_kargo'] ?? '') === 'kimia' ? 'selected' : ''; ?>>Kargo Bahan Kimia</option>
                                <option value="pecah_belah" <?php echo ($_POST['jenis_kargo'] ?? '') === 'pecah_belah' ? 'selected' : ''; ?>>Kargo Pecah Belah</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nama Pengirim *</label>
                            <input type="text" name="pengirim" class="form-input" value="<?php echo htmlspecialchars($_POST['pengirim'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Kota Tujuan *</label>
                            <input type="text" name="kota_tujuan" class="form-input" value="<?php echo htmlspecialchars($_POST['kota_tujuan'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Berat (kg) *</label>
                            <input type="number" name="berat" class="form-input" step="0.1" value="<?php echo htmlspecialchars($_POST['berat'] ?? ''); ?>" required>
                        </div>

                        <div id="fields-reguler" style="display: <?php echo ($_POST['jenis_kargo'] ?? '') === 'reguler' ? 'block' : 'none'; ?>;">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Jenis Paket</label>
                                    <select name="jenis_paket" class="form-select">
                                        <option <?php echo ($_POST['jenis_paket'] ?? 'Koli') === 'Koli' ? 'selected' : ''; ?>>Koli</option>
                                        <option <?php echo ($_POST['jenis_paket'] ?? '') === 'Dus' ? 'selected' : ''; ?>>Dus</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Estimasi Hari</label>
                                    <input type="number" name="estimasi_hari" class="form-input" value="<?php echo htmlspecialchars($_POST['estimasi_hari'] ?? '1'); ?>">
                                </div>
                            </div>
                        </div>

                        <div id="fields-kimia" style="display: <?php echo ($_POST['jenis_kargo'] ?? '') === 'kimia' ? 'block' : 'none'; ?>;">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Tingkat Bahaya (Class 1-9) *</label>
                                    <input type="number" name="tingkat_bahaya" class="form-input" min="1" max="9" value="<?php echo htmlspecialchars($_POST['tingkat_bahaya'] ?? '1'); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Sertifikasi Keamanan *</label>
                                    <input type="text" name="jenis_sertifikasi" class="form-input" value="<?php echo htmlspecialchars($_POST['jenis_sertifikasi'] ?? 'MSDS-SND'); ?>" placeholder="Contoh: MSDS-SND">
                                </div>
                            </div>
                            <small class="text-light" style="display: block; margin-top: 8px;">Biaya tambahan: Class × Rp 100.000 otomatis dihitung sistem.</small>
                        </div>

                        <div id="fields-pecah-belah" style="display: <?php echo ($_POST['jenis_kargo'] ?? '') === 'pecah_belah' ? 'block' : 'none'; ?>;">
                            <div class="form-group">
                                <label class="form-label">Ketebalan Bubble Wrap *</label>
                                <input type="text" name="ketebalan_bubble_wrap" class="form-input" value="<?php echo htmlspecialchars($_POST['ketebalan_bubble_wrap'] ?? '3 lapis'); ?>" placeholder="Contoh: 3 lapis">
                            </div>
                            <small class="text-light" style="display: block; margin-bottom: 16px;">Biaya Asuransi Wajib Rp 20.000 & Surcharge fragile 5% dari tarif berat otomatis dihitung sistem.</small>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">Buat Reservasi</button>
                    </form>
                </div>

                <div class="card">
                    <h3 class="card-title">Hasil Reservasi</h3>

                    <?php if ($result): ?>
                        <div style="background: var(--border-light); padding: 20px; border-radius: 8px;">
                            <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
                                <div style="font-size: 12px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 4px;">Jenis Kargo</div>
                                <div style="font-size: 16px; font-weight: 600; color: var(--primary-color);"><?php echo htmlspecialchars($result['jenis']); ?></div>
                            </div>

                            <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
                                <div style="font-size: 12px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 4px;">No. Resi</div>
                                <div style="font-size: 14px; font-weight: 600; font-family: 'Courier New', monospace; color: var(--accent-color);"><?php echo htmlspecialchars($result['resi']); ?></div>
                            </div>

                            <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
                                <div style="font-size: 12px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 4px;">Formula Perhitungan</div>
                                <div style="font-size: 12px; font-family: 'Courier New', monospace; color: var(--text-primary);"><?php echo htmlspecialchars($result['formula']); ?></div>
                            </div>

                            <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
                                <div style="font-size: 12px; color: var(--text-secondary); text-transform: uppercase; margin-bottom: 4px;">Status SOP Packing</div>
                                <div style="font-size: 14px; font-weight: 600; color: <?php echo $result['status_packing'] === 'TERPENUHI' ? 'var(--success)' : '#ef4444'; ?>;">
                                    <?php echo htmlspecialchars($result['status_packing']); ?>
                                </div>
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
