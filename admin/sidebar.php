<?php
// Shared Admin Sidebar
// This file is included by every page in /admin so the layout stays identical.
$current_page = basename($_SERVER['PHP_SELF']);

$admin_sidebar_items = [
    ['file' => 'dashboard.php',       'icon' => 'fa-chart-pie',              'label' => 'Dashboard'],
    ['file' => 'calendar.php',        'icon' => 'fa-calendar-days',           'label' => 'Calendar'],
    ['file' => 'profile.php',         'icon' => 'fa-user-gear',               'label' => 'Profile'],
    ['file' => 'manage_booking.php',  'icon' => 'fa-book',                    'label' => 'Bookings'],
    ['file' => 'manage_court.php',    'icon' => 'fa-table-tennis-paddle-ball', 'label' => 'Courts'],
    ['file' => 'manage_users.php',    'icon' => 'fa-users',                   'label' => 'Users'],
];
?>
<div class="sidebar">
    <a href="dashboard.php" class="sidebar-brand">
        <i class="fa-solid fa-shuttlecock text-warning"></i>
        <span>TailAdmin</span>
    </a>

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
