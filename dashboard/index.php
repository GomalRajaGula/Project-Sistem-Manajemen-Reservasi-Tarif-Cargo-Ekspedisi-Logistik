<?php
require_once __DIR__ . '/../classes/Kargo.php';

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Fetch real data from database
try {
    require_once __DIR__ . '/../config/connection.php';
    
    // Total Pengiriman
    $totalQuery = $pdo->query("SELECT COUNT(*) FROM kargo");
    $totalPengiriman = (int) $totalQuery->fetchColumn();
    
    // Kargo Reguler
    $regulerQuery = $pdo->query("SELECT COUNT(*) FROM kargo WHERE jenis_kargo = 'Reguler'");
    $kargoReguler = (int) $regulerQuery->fetchColumn();
    
    // Kargo Bahan Kimia
    $kimiaQuery = $pdo->query("SELECT COUNT(*) FROM kargo WHERE jenis_kargo = 'BahanKimia'");
    $kargoKimia = (int) $kimiaQuery->fetchColumn();
    
    // Kargo Pecah Belah
    $pecahBelahQuery = $pdo->query("SELECT COUNT(*) FROM kargo WHERE jenis_kargo = 'PecahBelah'");
    $kargoPecahBelah = (int) $pecahBelahQuery->fetchColumn();
    
    // Total Pendapatan
    $pendapatanQuery = $pdo->query("SELECT SUM(total_tarif) FROM kargo");
    $totalPendapatan = (float) $pendapatanQuery->fetchColumn();
    
    // Revenue per type for chart
    $revRegQuery = $pdo->query("SELECT SUM(total_tarif) FROM kargo WHERE jenis_kargo = 'Reguler'");
    $revReg = (float) $revRegQuery->fetchColumn();
    
    $revKimQuery = $pdo->query("SELECT SUM(total_tarif) FROM kargo WHERE jenis_kargo = 'BahanKimia'");
    $revKim = (float) $revKimQuery->fetchColumn();
    
    $revPecahQuery = $pdo->query("SELECT SUM(total_tarif) FROM kargo WHERE jenis_kargo = 'PecahBelah'");
    $revPecah = (float) $revPecahQuery->fetchColumn();

    // Riwayat Pengiriman (Limit 5)
    $riwayatQuery = $pdo->query("SELECT id_resi, jenis_kargo, pengirim, kota_tujuan, berat_barang, total_tarif FROM kargo ORDER BY tanggal_reservasi DESC LIMIT 5");
    $riwayatList = $riwayatQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Dashboard index error: " . $e->getMessage());
    $totalPengiriman = 0;
    $kargoReguler = 0;
    $kargoKimia = 0;
    $kargoPecahBelah = 0;
    $totalPendapatan = 0;
    $revReg = 0; $revKim = 0; $revPecah = 0;
    $riwayatList = [];
}

// Chart calculations
$pctReguler = $totalPengiriman > 0 ? ($kargoReguler / $totalPengiriman) * 100 : 0;
$pctKimia = $totalPengiriman > 0 ? ($kargoKimia / $totalPengiriman) * 100 : 0;
$pctPecahBelah = $totalPengiriman > 0 ? ($kargoPecahBelah / $totalPengiriman) * 100 : 0;

$maxRev = max($revReg, $revKim, $revPecah, 1);
$hReg = ($revReg / $maxRev) * 120;
$hKim = ($revKim / $maxRev) * 120;
$hPecah = ($revPecah / $maxRev) * 120;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cargo Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="container">

            <!-- HEADER -->
            <div class="page-header">
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Ikhtisar sistem manajemen cargo</p>
            </div>

            <!-- STATISTICS -->
            <div class="stats-grid">

                <div class="stat-card primary">
                    <div class="stat-card-header">
                        <div class="stat-icon">📦</div>
                    </div>
                    <div class="stat-label">Total Pengiriman</div>
                    <div class="stat-value"><?php echo $totalPengiriman; ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon">📫</div>
                    </div>
                    <div class="stat-label">Kargo Reguler</div>
                    <div class="stat-value"><?php echo $kargoReguler; ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon">⚗️</div>
                    </div>
                    <div class="stat-label">Kargo Bahan Kimia</div>
                    <div class="stat-value"><?php echo $kargoKimia; ?></div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon">🥂</div>
                    </div>
                    <div class="stat-label">Kargo Pecah Belah</div>
                    <div class="stat-value"><?php echo $kargoPecahBelah; ?></div>
                </div>

                <div class="stat-card success">
                    <div class="stat-card-header">
                        <div class="stat-icon">💰</div>
                    </div>
                    <div class="stat-label">Total Pendapatan</div>
                    <div class="stat-value"><?php echo formatRupiah($totalPendapatan); ?></div>
                </div>

            </div>

            <!-- DYNAMIC CHARTS -->
            <div class="charts-grid">

                <div class="chart-card">
                    <h3 class="chart-card-title">Distribusi Pengiriman</h3>
                    <div class="chart-container" style="display:flex; flex-direction:column; justify-content:center; padding: 10px;">
                        <?php if ($totalPengiriman === 0): ?>
                            <div style="text-align: center; color: #888;">Belum ada data pengiriman</div>
                        <?php else: ?>
                            <div style="width: 100%;">
                                <div style="margin-bottom: 16px;">
                                    <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:13px; font-weight:500;">
                                        <span style="color:#3b82f6;">📦 Kargo Reguler (<?php echo $kargoReguler; ?>)</span>
                                        <span><?php echo number_format($pctReguler, 1); ?>%</span>
                                    </div>
                                    <div style="width:100%; background: var(--border-color); height: 10px; border-radius: 5px;">
                                        <div style="width: <?php echo $pctReguler; ?>%; background: #3b82f6; height: 100%; border-radius: 5px; transition: width 0.5s ease-in-out;"></div>
                                    </div>
                                </div>
                                <div style="margin-bottom: 16px;">
                                    <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:13px; font-weight:500;">
                                        <span style="color:#f59e0b;">⚗️ Kargo Bahan Kimia (<?php echo $kargoKimia; ?>)</span>
                                        <span><?php echo number_format($pctKimia, 1); ?>%</span>
                                    </div>
                                    <div style="width:100%; background: var(--border-color); height: 10px; border-radius: 5px;">
                                        <div style="width: <?php echo $pctKimia; ?>%; background: #f59e0b; height: 100%; border-radius: 5px; transition: width 0.5s ease-in-out;"></div>
                                    </div>
                                </div>
                                <div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:13px; font-weight:500;">
                                        <span style="color:#ef4444;">🥂 Kargo Pecah Belah (<?php echo $kargoPecahBelah; ?>)</span>
                                        <span><?php echo number_format($pctPecahBelah, 1); ?>%</span>
                                    </div>
                                    <div style="width:100%; background: var(--border-color); height: 10px; border-radius: 5px;">
                                        <div style="width: <?php echo $pctPecahBelah; ?>%; background: #ef4444; height: 100%; border-radius: 5px; transition: width 0.5s ease-in-out;"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="chart-card">
                    <h3 class="chart-card-title">Pendapatan Per Jenis</h3>
                    <div class="chart-container" style="display:flex; align-items:center; justify-content:center;">
                        <?php if ($totalPendapatan == 0): ?>
                            <div style="color: #888;">Belum ada data pendapatan</div>
                        <?php else: ?>
                            <div style="display: flex; justify-content: space-around; align-items: flex-end; height: 170px; width: 100%; padding: 0 10px;">
                                <div style="display: flex; flex-direction: column; align-items: center; width: 30%;">
                                    <span style="font-size:11px; font-weight: 600; margin-bottom:6px; color: #3b82f6;"><?php echo formatRupiah($revReg); ?></span>
                                    <div style="width: 35px; height: <?php echo max($hReg, 8); ?>px; background: #3b82f6; border-radius: 4px 4px 0 0; transition: height 0.5s ease;"></div>
                                    <span style="font-size:12px; font-weight: 500; margin-top:8px; color: var(--text-primary);">Reguler</span>
                                </div>
                                <div style="display: flex; flex-direction: column; align-items: center; width: 30%;">
                                    <span style="font-size:11px; font-weight: 600; margin-bottom:6px; color: #f59e0b;"><?php echo formatRupiah($revKim); ?></span>
                                    <div style="width: 35px; height: <?php echo max($hKim, 8); ?>px; background: #f59e0b; border-radius: 4px 4px 0 0; transition: height 0.5s ease;"></div>
                                    <span style="font-size:12px; font-weight: 500; margin-top:8px; color: var(--text-primary);">Kimia</span>
                                </div>
                                <div style="display: flex; flex-direction: column; align-items: center; width: 30%;">
                                    <span style="font-size:11px; font-weight: 600; margin-bottom:6px; color: #ef4444;"><?php echo formatRupiah($revPecah); ?></span>
                                    <div style="width: 35px; height: <?php echo max($hPecah, 8); ?>px; background: #ef4444; border-radius: 4px 4px 0 0; transition: height 0.5s ease;"></div>
                                    <span style="font-size:12px; font-weight: 500; margin-top:8px; color: var(--text-primary);">Pecah Belah</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- TABLE -->
            <div class="table-card">
                <div class="table-header">
                    <h3 class="table-header-title">Riwayat Reservasi Pengiriman Terakhir</h3>
                </div>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>No Resi</th>
                                <th>Jenis Kargo</th>
                                <th>Pengirim</th>
                                <th>Kota Tujuan</th>
                                <th>Berat</th>
                                <th>Tarif Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (empty($riwayatList)): ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding:24px; color: var(--text-secondary);">
                                        Belum ada data reservasi pengiriman.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($riwayatList as $row): ?>
                                    <tr>
                                        <td><span class="badge" style="font-family: monospace; background: var(--border-color); color: var(--text-primary); font-size:13px; font-weight:600;"><?php echo htmlspecialchars($row['id_resi']); ?></span></td>
                                        <td>
                                            <?php 
                                            $jenisLabel = $row['jenis_kargo'];
                                            if ($jenisLabel === 'BahanKimia') $jenisLabel = 'Bahan Kimia';
                                            if ($jenisLabel === 'PecahBelah') $jenisLabel = 'Pecah Belah';
                                            echo htmlspecialchars($jenisLabel); 
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['pengirim']); ?></td>
                                        <td><?php echo htmlspecialchars($row['kota_tujuan']); ?></td>
                                        <td><strong><?php echo number_format($row['berat_barang'], 1, ',', '.'); ?> kg</strong></td>
                                        <td><strong style="color: var(--success); font-size:14px;"><?php echo formatRupiah($row['total_tarif']); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>
            </div>

        </div>
    </main>

</body>
</html>