<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../classes/Kargo.php';

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Handle deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id_resi'])) {
    $id_resi = $_GET['id_resi'];
    try {
        require_once __DIR__ . '/../config/connection.php';
        $pdo->beginTransaction();
        
        // Delete child
        $stmtChild = $pdo->prepare("DELETE FROM kargo_pecah_belah WHERE id_resi = ?");
        $stmtChild->execute([$id_resi]);
        
        // Delete parent
        $stmtParent = $pdo->prepare("DELETE FROM kargo WHERE id_resi = ?");
        $stmtParent->execute([$id_resi]);
        
        $pdo->commit();
        $_SESSION['success_message'] = "Reservasi kargo pecah belah dengan Resi $id_resi berhasil dihapus!";
    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error_message'] = "Gagal menghapus kargo: " . $e->getMessage();
    }
    header("Location: kargo_pecah_belah.php");
    exit;
}

// Fetch fragile cargos
try {
    require_once __DIR__ . '/../config/connection.php';
    $sql = "SELECT k.id_resi, k.pengirim, k.kota_tujuan, k.berat_barang, k.tarif_dasar_per_kg, k.total_tarif, k.status_packing, kp.ketebalan_bubble_wrap, kp.biaya_asuransi_wajib
            FROM kargo k
            JOIN kargo_pecah_belah kp ON k.id_resi = kp.id_resi
            ORDER BY k.tanggal_reservasi DESC";
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("kargo_pecah_belah.php error: " . $e->getMessage());
    $data = [];
}

$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Kargo Pecah Belah</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>

<main class="main-content">
<div class="container">

<h1 class="page-title">Data Kargo Pecah Belah</h1>

<?php if ($success_message): ?>
    <div style="background: rgba(16,185,129,0.1); border: 1px solid #10b981; color: #10b981; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px;">
        ✓ <?php echo htmlspecialchars($success_message); ?>
    </div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div style="background: rgba(239,68,68,0.1); border: 1px solid #ef4444; color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px;">
        ✗ <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

<div class="table-card">
<div class="table-wrapper">
<table>
<thead>
<tr>
<th>No Resi</th>
<th>Pengirim</th>
<th>Kota Tujuan</th>
<th>Berat</th>
<th>Bubble Wrap</th>
<th>Asuransi Wajib</th>
<th>Tarif/kg</th>
<th>Total Tarif</th>
<th>Status SOP</th>
<th>Aksi</th>
</tr>
</thead>

<tbody>
<?php if (empty($data)): ?>
<tr>
<td colspan="10" style="text-align:center; padding: 24px; color: var(--text-secondary);">Belum ada data kargo pecah belah</td>
</tr>
<?php else: ?>
<?php foreach ($data as $row): ?>
<tr>
<td><span class="badge" style="font-family: monospace; font-size:12px; font-weight:600;"><?php echo htmlspecialchars($row['id_resi']); ?></span></td>
<td><?php echo htmlspecialchars($row['pengirim']); ?></td>
<td><?php echo htmlspecialchars($row['kota_tujuan']); ?></td>
<td><strong><?php echo number_format($row['berat_barang'], 1, ',', '.'); ?> kg</strong></td>
<td><?php echo number_format($row['ketebalan_bubble_wrap'], 0); ?> lapis</td>
<td><?php echo formatRupiah($row['biaya_asuransi_wajib']); ?></td>
<td><?php echo formatRupiah($row['tarif_dasar_per_kg']); ?></td>
<td><strong style="color: var(--success);"><?php echo formatRupiah($row['total_tarif']); ?></strong></td>
<td>
    <span class="badge <?php echo $row['status_packing'] === 'TERPENUHI' ? 'success' : 'danger'; ?>" style="font-size: 11px;">
        <?php echo htmlspecialchars($row['status_packing']); ?>
    </span>
</td>
<td>
    <a href="kargo_pecah_belah.php?action=delete&id_resi=<?php echo urlencode($row['id_resi']); ?>" 
       class="btn btn-danger" 
       style="padding: 6px 12px; font-size: 11px; text-decoration: none; border-radius: 4px;"
       onclick="return confirm('Apakah Anda yakin ingin menghapus data reservasi ini?')">
       Hapus
    </a>
</td>
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