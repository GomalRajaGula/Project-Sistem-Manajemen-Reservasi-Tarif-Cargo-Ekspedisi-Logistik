<?php
// sidebar.php - Main Navigation Menu
$current_page = basename($_SERVER['PHP_SELF']);
$menu_items = [
    ['file' => 'index.php', 'label' => 'Dashboard', 'icon' => '🏠'],
    ['file' => 'kargo_reguler.php', 'label' => 'Data Kargo Reguler', 'icon' => '📦'],
    ['file' => 'kargo_kimia.php', 'label' => 'Data Kargo Kimia', 'icon' => '⚗️'],
    ['file' => 'kargo_pecah_belah.php', 'label' => 'Data Kargo Pecah Belah', 'icon' => '🥂'],
    ['file' => 'reservasi.php', 'label' => 'Reservasi Pengiriman', 'icon' => '📋'],
    ['file' => 'perhitungan_tarif.php', 'label' => 'Perhitungan Tarif', 'icon' => '💰'],
    ['file' => 'laporan.php', 'label' => 'Laporan', 'icon' => '📊']
];
?>
<aside class="sidebar">
    <nav class="sidebar-nav">
        <?php foreach ($menu_items as $item): ?>
            <a href="<?php echo $item['file']; ?>" class="nav-item <?php echo ($current_page === $item['file']) ? 'active' : ''; ?>">
                <span class="nav-icon"><?php echo $item['icon']; ?></span>
                <span class="nav-label"><?php echo $item['label']; ?></span>
                <?php if ($current_page === $item['file']): ?>
                    <span class="nav-indicator"></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>
