<?php
require_once __DIR__ . '/../classes/Kargo.php';
require_once __DIR__ . '/../classes/KargoPecahBelah.php';

$data = [
    ['PB001', 'PT Keramik Indah', 'Yogyakarta', 35, '5 lapis', 50000, 2150000],
    ['PB002', 'CV Elektronik Jaya', 'Solo', 28, '3 lapis', 75000, 1755000],
    ['PB003', 'Toko Kaca Besar', 'Bandung', 45, '7 lapis', 100000, 2800000],
    ['PB004', 'PT Furniture Premium', 'Surabaya', 32, '4 lapis', 60000, 1980000],
    ['PB005', 'CV Perhiasan Mewah', 'Jakarta', 10, '2 lapis', 40000, 640000],
];

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kargo Pecah Belah - Cargo Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Data Kargo Pecah Belah</h1>
                <p class="page-subtitle">Daftar pengiriman kargo fragile dengan perlindungan ekstra</p>
            </div>

            <!-- Data Table -->
            <div class="table-card">
                <div class="table-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="table-header-title">Tabel Kargo Pecah Belah</h3>
                        <button class="btn btn-primary btn-sm">+ Tambah Data</button>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>No Resi</th>
                                <th>Pengirim</th>
                                <th>Kota Tujuan</th>
                                <th>Berat (kg)</th>
                                <th>Ketebalan Bubble Wrap</th>
                                <th>Biaya Asuransi</th>
                                <th>Tarif Pengiriman</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $row): ?>
                            <tr>
                                <td><strong><?php echo $row[0]; ?></strong></td>
                                <td><?php echo $row[1]; ?></td>
                                <td><?php echo $row[2]; ?></td>
                                <td><?php echo $row[3]; ?></td>
                                <td><span class="badge badge-success"><?php echo $row[4]; ?></span></td>
                                <td><?php echo formatRupiah($row[5]); ?></td>
                                <td><strong class="text-success"><?php echo formatRupiah($row[6]); ?></strong></td>
                                <td>
                                    <button class="btn btn-secondary btn-sm">✎</button>
                                    <button class="btn btn-secondary btn-sm">🗑</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-20">
                <p class="text-light">Total: <?php echo count($data); ?> data | Total Pendapatan: <strong><?php echo formatRupiah(array_sum(array_column($data, 6))); ?></strong></p>
            </div>
        </div>
    </main>
</body>
</html>
