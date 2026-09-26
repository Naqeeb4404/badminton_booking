<?php
// Shared Admin Sidebar
// This file is included by every page in /admin so the layout stays identical.
$current_page = basename($_SERVER['PHP_SELF']);

$current_admin = $admin ?? $_SESSION['user'] ?? ['name' => 'Admin', 'phone' => 'Admin Panel', 'profile_pic' => ''];

$admin_sidebar_items = [
    ['file' => 'dashboard.php',       'icon' => 'fa-chart-pie',            'label' => 'Dashboard'],
    ['file' => 'calendar.php',        'icon' => 'fa-calendar-days',        'label' => 'Calendar'],
    ['file' => 'profile.php',         'icon' => 'fa-user-gear',            'label' => 'Profile'],
    ['file' => 'manage_booking.php',  'icon' => 'fa-book',                 'label' => 'Bookings'],
    ['file' => 'manage_court.php',    'icon' => 'fa-table-tennis-paddle-ball', 'label' => 'Courts'],
    ['file' => 'manage_users.php',    'icon' => 'fa-users',                'label' => 'Users'],
];
?>
<div class="sidebar">
    <!-- Profil Admin -->
    <div class="sidebar-profile-card">
        <div class="sidebar-profile-main">
            <div class="sidebar-avatar-wrap">
                <div class="sidebar-avatar" style="<?php echo !empty($current_admin['profile_pic']) ? "background-image:url(\"../uploads/" . htmlspecialchars($current_admin['profile_pic'], ENT_QUOTES, 'UTF-8') . "\");" : ''; ?>">
                    <?php echo empty($current_admin['profile_pic']) ? htmlspecialchars(strtoupper(substr($current_admin['name'] ?? 'A', 0, 1)), ENT_QUOTES, 'UTF-8') : ''; ?>
                </div>
                <span class="sidebar-online-dot"></span>
            </div>
            <div class="sidebar-profile-text">
                <div class="sidebar-profile-name"><?php echo htmlspecialchars($current_admin['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="sidebar-profile-role"><i class="fa-solid fa-shield-halved"></i> Super Admin</div>
            </div>
        </div>
        <a href="profile.php" class="sidebar-settings" title="Admin Profile"><i class="fa-solid fa-gear"></i></a>
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

        <div class="menu-label support-label">Sokongan (Support)</div>

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

        <!-- Menu Laporan Pendapatan -->
        <a href="monthly_revenue.php" class="sidebar-nav-link<?php echo $current_page === 'monthly_revenue.php' ? ' active' : ''; ?>">
            <div class="sidebar-nav-link-content">
                <i class="fa-solid fa-chart-line"></i>
                <span>Revenue Report</span>
            </div>
        </a>

        <!-- Menu Laporan Harian -->
        <a href="daily_report.php" class="sidebar-nav-link<?php echo $current_page === 'daily_report.php' ? ' active' : ''; ?>">
            <div class="sidebar-nav-link-content">
                <i class="fa-solid fa-calendar-day"></i>
                <span>Daily Report</span>
            </div>
        </a>
    </div>
</div>