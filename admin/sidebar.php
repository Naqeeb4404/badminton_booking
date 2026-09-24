<?php
// Shared Admin Sidebar
// This file is included by every page in /admin so the layout stays identical.
$current_page = basename($_SERVER['PHP_SELF']);

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
    <!-- Bahagian Logo / Jenama dengan Kesan Moden -->
    <a href="dashboard.php" class="sidebar-brand" style="display: flex; align-items: center; gap: 12px; padding: 22px 20px; text-decoration: none; color: #ffffff;">
        <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);">
            <i class="fa-solid fa-shuttlecock" style="color: #fff; font-size: 0.9rem;"></i>
        </div>
        <span style="font-weight: 700; font-size: 1.1rem; letter-spacing: 0.5px; background: linear-gradient(90deg, #fff, #cbd5e1); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">TailAdmin</span>
    </a>

    <!-- Kad Profil Pengguna Bergaya Moden & Interaktif -->
    <div class="sidebar-profile-card" style="margin: 0 15px 18px 15px; padding: 14px; background: linear-gradient(135deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.9)); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 14px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); display: flex; align-items: center; justify-content: space-between; position: relative; overflow: hidden;">
        
        <!-- Kesan Cahaya Latar Belakang Halus -->
        <div style="position: absolute; top: -50px; right: -50px; width: 100px; height: 100px; background: rgba(37, 99, 235, 0.15); border-radius: 50%; filter: blur(20px);"></div>

        <div style="display: flex; align-items: center; gap: 12px; overflow: hidden; z-index: 1;">
            <!-- Avatar Bulat Berserta Status Dot Hijau -->
            <div style="position: relative; flex-shrink: 0;">
                <div style="width: 42px; height: 42px; border-radius: 50%; background: #0f172a; border: 2px solid rgba(255, 255, 255, 0.15); background-image: url('<?php echo !empty($current_admin['profile_pic']) ? '../uploads/'.htmlspecialchars($current_admin['profile_pic'], ENT_QUOTES, 'UTF-8') : ''; ?>'); background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 0.85rem;">
                    <?php echo empty($current_admin['profile_pic']) ? htmlspecialchars(strtoupper(substr($current_admin['name'] ?? 'A', 0, 1)), ENT_QUOTES, 'UTF-8') : ''; ?>
                </div>
                <!-- Status Online Dot -->
                <span style="position: absolute; bottom: 0; right: 0; width: 10px; height: 10px; background-color: #22c55e; border: 2px solid #0f172a; border-radius: 50%;"></span>
            </div>

            <!-- Nama & Info -->
            <div style="overflow: hidden;">
                <h4 style="margin: 0 0 2px 0; font-size: 0.88rem; font-weight: 600; color: #ffffff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($current_admin['name'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></h4>
                <span style="font-size: 0.72rem; color: #94a3b8; display: flex; align-items: center; gap: 4px;"><i class="fa-solid fa-shield-halved" style="font-size: 0.6rem; color: #38bdf8;"></i> Super Admin</span>
            </div>
        </div>

        <!-- Ikon Tetapan (Gear) dengan Animasi Hover -->
        <a href="profile.php" style="width: 32px; height: 32px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 8px; color: #94a3b8; font-size: 0.85rem; text-decoration: none; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; z-index: 1;" title="Tetapan Profil" onmouseover="this.style.background='rgba(37, 99, 235, 0.2)'; this.style.color='#38bdf8'; this.style.borderColor='rgba(37, 99, 235, 0.4)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.05)'; this.style.color='#94a3b8'; this.style.borderColor='rgba(255, 255, 255, 0.08)'">
            <i class="fa-solid fa-gear"></i>
        </a>
    </div>

    <div class="sidebar-menu" style="padding: 0 10px;">
        <div class="menu-label" style="padding: 0 10px 8px 10px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: #64748b; font-weight: 700;">Menu Utama</div>

        <?php foreach ($admin_sidebar_items as $item): ?>
            <a href="<?php echo htmlspecialchars($item['file']); ?>"
               class="sidebar-nav-link<?php echo $current_page === $item['file'] ? ' active' : ''; ?>">
                <div class="sidebar-nav-link-content">
                    <i class="fa-solid <?php echo htmlspecialchars($item['icon']); ?>"></i>
                    <span><?php echo htmlspecialchars($item['label']); ?></span>
                </div>
            </a>
        <?php endforeach; ?>

        <div class="menu-label mt-3" style="padding: 15px 10px 8px 10px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: #64748b; font-weight: 700;">Sokongan (Support)</div>

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