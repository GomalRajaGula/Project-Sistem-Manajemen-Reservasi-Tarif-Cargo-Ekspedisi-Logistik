<?php
require_once __DIR__ . '/../classes/Kargo.php';
require_once __DIR__ . '/../classes/KargoReguler.php';

$data = [
    ['REG001', 'PT Maju Jaya', 'Surabaya', 25, 'Koli', 3, 1250000],
    ['REG002', 'CV Express', 'Bandung', 15, 'Dus', 2, 750000],
    ['REG003', 'Warehouse Central', 'Jakarta', 50, 'Koli', 1, 2500000],
    ['REG004', 'PT Logistik Cepat', 'Medan', 30, 'Dus', 4, 1500000],
    ['REG005', 'CV Pengiriman Jaya', 'Semarang', 20, 'Koli', 2, 1000000],
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
    <title>Data Kargo Reguler - Cargo Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Data Kargo Reguler</h1>
                <p class="page-subtitle">Daftar lengkap pengiriman kargo jenis reguler</p>
            </div>

            <!-- Data Table -->
            <div class="table-card">
                <div class="table-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="table-header-title">Tabel Kargo Reguler</h3>
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
                                <th>Jenis Paket</th>
                                <th>Estimasi Hari</th>
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
                                <td><span class="badge badge-primary"><?php echo $row[4]; ?></span></td>
                                <td><?php echo $row[5]; ?> hari</td>
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
