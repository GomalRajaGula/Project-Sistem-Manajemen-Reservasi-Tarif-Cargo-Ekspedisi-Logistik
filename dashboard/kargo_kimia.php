<?php
require_once __DIR__ . '/../classes/Kargo.php';
require_once __DIR__ . '/../classes/KargoBahanKimia.php';

$data = [
    ['KIM001', 'PT Kimia Indonesia', 'Medan', 30, 'Class 5', 'ISO-1234', 3375000],
    ['KIM002', 'CV Chemical Supply', 'Makassar', 20, 'Class 7', 'ISO-5678', 3000000],
    ['KIM003', 'Warehouse Khusus', 'Semarang', 40, 'Class 3', 'ISO-9012', 2880000],
    ['KIM004', 'PT Bahan Baku', 'Palembang', 25, 'Class 6', 'ISO-3456', 2812500],
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
    <title>Data Kargo Bahan Kimia - Cargo Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">Data Kargo Bahan Kimia</h1>
                <p class="page-subtitle">Daftar pengiriman kargo berbahaya dengan sertifikasi</p>
            </div>

            <!-- Data Table -->
            <div class="table-card">
                <div class="table-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="table-header-title">Tabel Kargo Bahan Kimia</h3>
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
                                <th>Tingkat Bahaya</th>
                                <th>Sertifikasi</th>
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
                                <td>
                                    <?php 
                                    $class = substr($row[4], 6);
                                    if ($class <= 3) {
                                        echo '<span class="badge badge-primary">' . $row[4] . ' (Rendah)</span>';
                                    } elseif ($class <= 6) {
                                        echo '<span class="badge badge-warning">' . $row[4] . ' (Sedang)</span>';
                                    } else {
                                        echo '<span class="badge badge-danger">' . $row[4] . ' (Tinggi)</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo $row[5]; ?></td>
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
