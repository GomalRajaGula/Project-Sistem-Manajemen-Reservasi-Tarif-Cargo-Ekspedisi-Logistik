<?php
require_once __DIR__ . '/../classes/Kargo.php';

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
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

            <!-- STATISTICS (KOSONG) -->
            <div class="stats-grid">

                <div class="stat-card primary">
                    <div class="stat-card-header">
                        <div class="stat-icon">📦</div>
                    </div>
                    <div class="stat-label">Total Pengiriman</div>
                    <div class="stat-value">0</div>
                    <div class="stat-change">Belum ada data</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon">📫</div>
                    </div>
                    <div class="stat-label">Kargo Reguler</div>
                    <div class="stat-value">0</div>
                    <div class="stat-change">Belum ada data</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon">⚗️</div>
                    </div>
                    <div class="stat-label">Kargo Bahan Kimia</div>
                    <div class="stat-value">0</div>
                    <div class="stat-change">Belum ada data</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon">🥂</div>
                    </div>
                    <div class="stat-label">Kargo Pecah Belah</div>
                    <div class="stat-value">0</div>
                    <div class="stat-change">Belum ada data</div>
                </div>

                <div class="stat-card success">
                    <div class="stat-card-header">
                        <div class="stat-icon">💰</div>
                    </div>
                    <div class="stat-label">Total Pendapatan</div>
                    <div class="stat-value">Rp 0</div>
                    <div class="stat-change">Belum ada data</div>
                </div>

            </div>

            <!-- CHART PLACEHOLDER (KOSONG) -->
            <div class="charts-grid">

                <div class="chart-card">
                    <h3 class="chart-card-title">Distribusi Pengiriman</h3>
                    <div class="chart-container"
                         style="display:flex;align-items:center;justify-content:center;color:#888;">
                        Belum ada data chart
                    </div>
                </div>

                <div class="chart-card">
                    <h3 class="chart-card-title">Pendapatan</h3>
                    <div class="chart-container"
                         style="display:flex;align-items:center;justify-content:center;color:#888;">
                        Belum ada data chart
                    </div>
                </div>

            </div>

            <!-- TABLE (KOSONG) -->
            <div class="table-card">
                <div class="table-header">
                    <h3 class="table-header-title">Riwayat Pengiriman</h3>
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
                                <th>Tarif</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td colspan="6" style="text-align:center; padding:20px;">
                                    Belum ada data pengiriman
                                </td>
                            </tr>
                        </tbody>

                    </table>
                </div>
            </div>

        </div>
    </main>

</body>
</html>