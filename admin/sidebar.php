<?php
// Shared Admin Sidebar
// This file is included by every page in /admin so the layout stays identical.
$current_page = basename($_SERVER['PHP_SELF']);

$current_admin = $_SESSION['user'] ?? ['name' => 'Admin', 'phone' => 'Admin Panel', 'profile_pic' => ''];

// Always fetch the latest admin record. This is the single source of truth for
// the sidebar avatar and the top-right avatar on every admin page.
if (isset($conn) && !empty($_SESSION['user']['id'])) {
    $sidebar_admin_id = (int) $_SESSION['user']['id'];
    $sidebar_stmt = mysqli_prepare($conn, "SELECT id, name, email, phone, role, profile_pic FROM users WHERE id = ? LIMIT 1");
    if ($sidebar_stmt) {
        mysqli_stmt_bind_param($sidebar_stmt, "i", $sidebar_admin_id);
        mysqli_stmt_execute($sidebar_stmt);
        $sidebar_result = mysqli_stmt_get_result($sidebar_stmt);
        $fresh_admin = $sidebar_result ? mysqli_fetch_assoc($sidebar_result) : null;
        mysqli_stmt_close($sidebar_stmt);
        if ($fresh_admin) {
            $current_admin = $fresh_admin;
            $_SESSION['user']['name'] = $fresh_admin['name'];
            $_SESSION['user']['email'] = $fresh_admin['email'];
            $_SESSION['user']['phone'] = $fresh_admin['phone'];
            $_SESSION['user']['role'] = $fresh_admin['role'];
            $_SESSION['user']['profile_pic'] = $fresh_admin['profile_pic'];
        }
    }
}

$admin_photo = trim((string)($current_admin['profile_pic'] ?? ''));
$admin_photo_url = '';
if ($admin_photo !== '') {
    $admin_photo_path = __DIR__ . '/../uploads/' . basename($admin_photo);
    $admin_photo_version = is_file($admin_photo_path) ? (string) filemtime($admin_photo_path) : (string) time();
    $admin_photo_url = '../uploads/' . rawurlencode(basename($admin_photo)) . '?v=' . $admin_photo_version;
}
$admin_photo_style = $admin_photo_url !== ''
    ? "background-image:url('" . htmlspecialchars($admin_photo_url, ENT_QUOTES, 'UTF-8') . "'); background-size:cover; background-position:center; background-repeat:no-repeat;"
    : '';
$admin_initial = strtoupper(substr((string)($current_admin['name'] ?? 'A'), 0, 1));

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
                <div class="sidebar-avatar" style="<?php echo $admin_photo_style; ?>">
                    <?php echo $admin_photo === '' ? htmlspecialchars($admin_initial, ENT_QUOTES, 'UTF-8') : ''; ?>
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