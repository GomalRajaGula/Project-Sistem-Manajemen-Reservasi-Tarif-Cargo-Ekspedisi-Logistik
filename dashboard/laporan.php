<?php
require_once __DIR__ . '/../classes/Kargo.php';

// Sample report data
$monthlyData = [
    'Jan' => ['pengiriman' => 45, 'pendapatan' => 2800000],
    'Feb' => ['pengiriman' => 52, 'pendapatan' => 3100000],
    'Mar' => ['pengiriman' => 48, 'pendapatan' => 2900000],
    'Apr' => ['pengiriman' => 61, 'pendapatan' => 3500000],
    'Mei' => ['pengiriman' => 72, 'pendapatan' => 4100000],
    'Jun' => ['pengiriman' => 58, 'pendapatan' => 3500000],
];

$cargoTypeData = [
    ['Reguler', 95, 8500000],
    ['Bahan Kimia', 28, 4500000],
    ['Pecah Belah', 32, 5200000],
];

$topCities = [
    ['Jakarta', 38],
    ['Surabaya', 25],
    ['Bandung', 18],
    ['Medan', 12],
    ['Semarang', 10],
];

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

$totalPengiriman = array_sum(array_column($monthlyData, 'pengiriman'));
$totalPendapatan = array_sum(array_column($monthlyData, 'pendapatan'));
$averageValue = $totalPendapatan / $totalPengiriman;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Analitik - Cargo Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Laporan & Analitik</h1>
                <p class="page-subtitle">Analisis data pengiriman dan pendapatan</p>
            </div>

            <!-- Key Metrics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon">📊</div>
                    </div>
                    <div class="stat-label">Total Pengiriman</div>
                    <div class="stat-value"><?php echo $totalPengiriman; ?></div>
                    <div class="stat-change">6 bulan terakhir</div>
                </div>

                <div class="stat-card success">
                    <div class="stat-card-header">
                        <div class="stat-icon">💵</div>
                    </div>
                    <div class="stat-label">Total Pendapatan</div>
                    <div class="stat-value"><?php echo number_format($totalPendapatan/1000000, 1, ',', '.'); ?>M</div>
                    <div class="stat-change">6 bulan terakhir</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon">📈</div>
                    </div>
                    <div class="stat-label">Nilai Rata-rata</div>
                    <div class="stat-value"><?php echo number_format($averageValue/1000, 0, ',', '.'); ?>K</div>
                    <div class="stat-change">per pengiriman</div>
                </div>

                <div class="stat-card primary">
                    <div class="stat-card-header">
                        <div class="stat-icon">⭐</div>
                    </div>
                    <div class="stat-label">Jenis Paling Laku</div>
                    <div class="stat-value"><?php echo $cargoTypeData[0][0]; ?></div>
                    <div class="stat-change"><?php echo $cargoTypeData[0][1]; ?> pengiriman</div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h3 class="chart-card-title">Tren Pengiriman Bulanan</h3>
                    <div class="chart-container">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <h3 class="chart-card-title">Distribusi Jenis Kargo</h3>
                    <div class="chart-container">
                        <canvas id="typeChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Detailed Tables -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Monthly Report -->
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-header-title">Laporan Bulanan</h3>
                    </div>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Bulan</th>
                                    <th>Pengiriman</th>
                                    <th>Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($monthlyData as $month => $data): ?>
                                <tr>
                                    <td><strong><?php echo $month; ?></strong></td>
                                    <td><?php echo $data['pengiriman']; ?></td>
                                    <td><?php echo formatRupiah($data['pendapatan']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Cities -->
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-header-title">Kota Tujuan Terbanyak</h3>
                    </div>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Kota</th>
                                    <th>Pengiriman</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topCities as $city): ?>
                                <tr>
                                    <td><strong><?php echo $city[0]; ?></strong></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <div style="flex: 1; height: 20px; background: rgba(37,99,235,0.2); border-radius: 4px; position: relative; overflow: hidden;">
                                                <div style="height: 100%; background: var(--accent-color); width: <?php echo ($city[1]/40)*100; ?>%;"></div>
                                            </div>
                                            <span style="min-width: 30px; text-align: right; font-weight: 500;"><?php echo $city[1]; ?></span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Cargo Type Report -->
            <div class="table-card" style="margin-top: 20px;">
                <div class="table-header">
                    <h3 class="table-header-title">Laporan Berdasarkan Jenis Kargo</h3>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Jenis Kargo</th>
                                <th>Jumlah Pengiriman</th>
                                <th>Total Pendapatan</th>
                                <th>Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cargoTypeData as $type): ?>
                            <tr>
                                <td><strong><?php echo $type[0]; ?></strong></td>
                                <td><?php echo $type[1]; ?></td>
                                <td><?php echo formatRupiah($type[2]); ?></td>
                                <td>
                                    <span class="badge badge-primary"><?php echo number_format(($type[1]/$totalPengiriman)*100, 1, ',', '.'); ?>%</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
                datasets: [{
                    label: 'Pengiriman',
                    data: [45, 52, 48, 61, 72, 58],
                    backgroundColor: '#2563EB',
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Type Distribution Chart
        const typeCtx = document.getElementById('typeChart').getContext('2d');
        new Chart(typeCtx, {
            type: 'pie',
            data: {
                labels: ['Kargo Reguler', 'Bahan Kimia', 'Pecah Belah'],
                datasets: [{
                    data: [95, 28, 32],
                    backgroundColor: ['#2563EB', '#F59E0B', '#EF4444'],
                    borderColor: '#FFFFFF',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { size: 12 }, padding: 20 }
                    }
                }
            }
        });
    </script>
</body>
</html>
