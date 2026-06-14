<?php
require_once __DIR__ . '/../classes/Kargo.php';

$data = [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Kargo Kimia</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>

<main class="main-content">
<div class="container">

<h1 class="page-title">Data Kargo Bahan Kimia</h1>

<div class="table-card">
<table>
<thead>
<tr>
<th>No Resi</th>
<th>Pengirim</th>
<th>Kota</th>
<th>Berat</th>
<th>Tarif /kg</th>
<th>Aksi</th>
</tr>
</thead>

<tbody>
<tr>
<td colspan="6" style="text-align:center;">Belum ada data</td>
</tr>
</tbody>

</table>
</div>

</div>
</main>

</body>
</html>