<?php
// navbar.php - Simple Clean Navigation
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <div class="navbar-container">
        <div class="navbar-left">
            <div class="navbar-logo">
                <span class="logo-icon">📦</span>
                <span class="logo-text">CargoFlow</span>
            </div>
        </div>
        <div class="navbar-right">
            <div class="navbar-info">
                <span class="info-label">Administrator</span>
                <span class="info-value">Sistem Manajemen Cargo</span>
            </div>
            <div class="navbar-divider"></div>
            <div class="navbar-user">
                <div class="user-avatar">A</div>
                <span class="user-name">Admin</span>
            </div>
        </div>
    </div>
</nav>
