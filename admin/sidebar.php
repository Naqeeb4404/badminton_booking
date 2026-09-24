<?php
// Shared Admin Sidebar
// This file is included by every page in /admin so the layout stays identical.
$current_page = basename($_SERVER['PHP_SELF']);

// Pastikan data admin wujud (menggunakan $_SESSION['user'] jika pembolehubah $admin belum didefinisikan)
$current_admin = $admin ?? $_SESSION['user'] ?? ['name' => 'Admin', 'phone' => 'Admin Panel', 'profile_pic' => ''];

$admin_sidebar_items = [
    ['file' => 'dashboard.php',        'icon' => 'fa-chart-pie',             'label' => 'Dashboard'],
    ['file' => 'calendar.php',         'icon' => 'fa-calendar-days',           'label' => 'Calendar'],
    ['file' => 'profile.php',          'icon' => 'fa-user-gear',               'label' => 'Profile'],
    ['file' => 'manage_booking.php',  'icon' => 'fa-book',                    'label' => 'Bookings'],
    ['file' => 'manage_court.php',    'icon' => 'fa-table-tennis-paddle-ball', 'label' => 'Courts'],
    ['file' => 'manage_users.php',    'icon' => 'fa-users',                   'label' => 'Users'],
];
?>
<div class="sidebar">
    <!-- Bahagian Logo / Jenama -->
    <a href="dashboard.php" class="sidebar-brand">
        <i class="fa-solid fa-shuttlecock text-warning"></i>
        <span>TailAdmin</span>
    </a>

    <!-- Bahagian Profil Pengguna di Sidebar (Gaya Rujukan) -->
    <div class="sidebar-profile" style="padding: 12px 15px; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid rgba(255, 255, 255, 0.08); border-bottom: 1px solid rgba(255, 255, 255, 0.08); background: rgba(0, 0, 0, 0.15); margin-bottom: 10px;">
        <div style="display: flex; align-items: center; gap: 10px; overflow: hidden;">
            <!-- Avatar Bulat -->
            <div style="width: 36px; height: 36px; border-radius: 50%; background: #1e293b; background-image: url('<?php echo !empty($current_admin['profile_pic']) ? '../uploads/'.htmlspecialchars($current_admin['profile_pic'], ENT_QUOTES, 'UTF-8') : ''; ?>'); background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 0.8rem; flex-shrink: 0;">
                <?php echo empty($current_admin['profile_pic']) ? htmlspecialchars(strtoupper(substr($current_admin['name'] ?? 'A', 0, 1)), ENT_QUOTES, 'UTF-8') : ''; ?>
            </div>
            <!-- Nama & Info -->
            <div style="overflow: hidden;">
                <h4 style="margin: 0; font-size: 0.82rem; font-weight: 600; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($current_admin['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></h4>
                <span style="font-size: 0.7rem; color: #94a3b8; display: flex; align-items: center; gap: 3px;"><i class="fa-solid fa-location-dot" style="font-size: 0.55rem;"></i> <?php echo htmlspecialchars($current_admin['phone'] ?? 'Admin Panel', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>
        <!-- Ikon Tetapan (Gear) ke Halaman Profil -->
        <a href="profile.php" style="color: #94a3b8; font-size: 0.85rem; text-decoration: none; transition: 0.2s; display: flex; align-items: center; justify-content: center;" title="Tetapan Profil">
            <i class="fa-solid fa-gear"></i>
        </a>
    </div>

    <div class="sidebar-menu">
        <div class="menu-label">Menu Utama</div>

        <?php foreach ($admin_sidebar_items as $item): ?>
            <a href="<?php echo htmlspecialchars($item['file']); ?>"
               class="sidebar-nav-link<?php echo $current_page === $item['file'] ? ' active' : ''; ?>">
                <div class="sidebar-nav-link-content">
                    <i class="fa-solid <?php echo htmlspecialchars($item['icon']); ?>"></i>
                    <span><?php echo htmlspecialchars($item['label']); ?></span>
                </div>
            </a>
        <?php endforeach; ?>

        <div class="menu-label mt-3">Sokongan (Support)</div>

        <a href="message.php" class="sidebar-nav-link<?php echo $current_page === 'message.php' ? ' active' : ''; ?>">
            <div class="sidebar-nav-link-content">
                <i class="fa-solid fa-comments"></i>
                <span>Message</span>
            </div>
        </a>

        <a href="manage_payment.php" class="sidebar-nav-link<?php echo $current_page === 'manage_payment.php' ? ' active' : ''; ?>">
            <div class="sidebar-nav-link-content">
                <i class="fa-solid fa-file-invoice-dollar"></i>
                <span>Invoice / Payments</span>
            </div>
        </a>
    </div>
</div>