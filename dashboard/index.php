<?php
require_once __DIR__ . '/../classes/Kargo.php';
require_once __DIR__ . '/../classes/KargoReguler.php';
require_once __DIR__ . '/../classes/KargoBahanKimia.php';
require_once __DIR__ . '/../classes/KargoPecahBelah.php';

// Sample Data
$regularData = [
    ['REG001', 'PT Maju Jaya', 'Surabaya', 25, 'Koli', 3, 1250000],
    ['REG002', 'CV Express', 'Bandung', 15, 'Dus', 2, 750000],
    ['REG003', 'Warehouse Central', 'Jakarta', 50, 'Koli', 1, 2500000],
];

$kimiaData = [
    ['KIM001', 'PT Kimia Indonesia', 'Medan', 30, 'Class 5', 'ISO-1234', 3375000],
    ['KIM002', 'CV Chemical', 'Makassar', 20, 'Class 7', 'ISO-5678', 3000000],
];

$pecahBelahData = [
    ['PB001', 'PT Keramik', 'Yogyakarta', 35, '5 lapis', 50000, 2150000],
    ['PB002', 'CV Elektronik', 'Solo', 28, '3 lapis', 75000, 1755000],
    ['PB003', 'Toko Kaca', 'Bandung', 45, '7 lapis', 100000, 2800000],
];

$stats = [
    'total_pengiriman' => count($regularData) + count($kimiaData) + count($pecahBelahData),
    'total_reguler' => count($regularData),
    'total_kimia' => count($kimiaData),
    'total_pecah_belah' => count($pecahBelahData),
    'total_pendapatan' => array_sum(array_column($regularData, 6)) + 
                         array_sum(array_column($kimiaData, 6)) + 
                         array_sum(array_column($pecahBelahData, 6))
];

$allData = array_merge(
    array_map(fn($x) => ['Reguler', ...$x], $regularData),
    array_map(fn($x) => ['Kimia', ...$x], $kimiaData),
    array_map(fn($x) => ['Pecah Belah', ...$x], $pecahBelahData)
);

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Ikhtisar operasional sistem manajemen cargo</p>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-card-header">
                        <div class="stat-icon">📦</div>
                    </div>
                    <div class="stat-label">Total Pengiriman</div>
                    <div class="stat-value"><?php echo $stats['total_pengiriman']; ?></div>
                    <div class="stat-change">↑ 12% dibanding bulan lalu</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon">📫</div>
                    </div>
                    <div class="stat-label">Kargo Reguler</div>
                    <div class="stat-value"><?php echo $stats['total_reguler']; ?></div>
                    <div class="stat-change">Jenis standar</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon">⚗️</div>
                    </div>
                    <div class="stat-label">Kargo Bahan Kimia</div>
                    <div class="stat-value"><?php echo $stats['total_kimia']; ?></div>
                    <div class="stat-change">Jenis berbahaya</div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon">🥂</div>
                    </div>
                    <div class="stat-label">Kargo Pecah Belah</div>
                    <div class="stat-value"><?php echo $stats['total_pecah_belah']; ?></div>
                    <div class="stat-change">Dengan asuransi</div>
                </div>

                <div class="stat-card success">
                    <div class="stat-card-header">
                        <div class="stat-icon">💰</div>
                    </div>
                    <div class="stat-label">Total Pendapatan</div>
                    <div class="stat-value"><?php echo number_format($stats['total_pendapatan']/1000000, 1, ',', '.'); ?>M</div>
                    <div class="stat-change">↑ 18% dibanding bulan lalu</div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h3 class="chart-card-title">Distribusi Pengiriman Berdasarkan Jenis</h3>
                    <div class="chart-container">
                        <canvas id="deliveryChart"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <h3 class="chart-card-title">Pendapatan 12 Bulan Terakhir</h3>
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Shipments -->
            <div class="table-card">
                <div class="table-header">
                    <h3 class="table-header-title">Riwayat Pengiriman Terbaru</h3>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>No Resi</th>
                                <th>Jenis Kargo</th>
                                <th>Pengirim</th>
                                <th>Kota Tujuan</th>
                                <th>Berat (kg)</th>
                                <th>Tarif Pengiriman</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($allData, 0, 6) as $data): ?>
                            <tr>
                                <td><strong><?php echo $data[1]; ?></strong></td>
                                <td>
                                    <?php if ($data[0] === 'Reguler'): ?>
                                        <span class="badge badge-primary">Reguler</span>
                                    <?php elseif ($data[0] === 'Kimia'): ?>
                                        <span class="badge badge-warning">Bahan Kimia</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Pecah Belah</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $data[2]; ?></td>
                                <td><?php echo $data[3]; ?></td>
                                <td><?php echo $data[4]; ?></td>
                                <td><strong><?php echo formatRupiah($data[count($data)-1]); ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Delivery Type Chart
        const deliveryCtx = document.getElementById('deliveryChart').getContext('2d');
        new Chart(deliveryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Kargo Reguler', 'Kargo Bahan Kimia', 'Kargo Pecah Belah'],
                datasets: [{
                    data: [<?php echo $stats['total_reguler']; ?>, <?php echo $stats['total_kimia']; ?>, <?php echo $stats['total_pecah_belah']; ?>],
                    backgroundColor: ['#2563EB', '#F59E0B', '#EF4444'],
                    borderColor: '#FFFFFF',
                    borderWidth: 2,
                    borderRadius: 4
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

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Pendapatan',
                    data: [2800000, 3100000, 2900000, 3500000, 4100000, <?php echo $stats['total_pendapatan']; ?>, 4300000, 4600000, 4900000, 5200000, 5400000, 5700000],
                    borderColor: '#2563EB',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#2563EB',
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value/1000000).toFixed(1) + 'M';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
