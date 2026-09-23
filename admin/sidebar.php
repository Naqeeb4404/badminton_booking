<!-- SIDEBAR -->
<div class="sidebar">
    <a href="dashboard.php" class="sidebar-brand">
        <i class="fa-solid fa-shuttlecock text-warning"></i>
        <span>TailAdmin</span>
    </a>

    <div class="sidebar-menu">
        <div class="menu-label">Menu Utama</div>
        <a href="dashboard.php" class="sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <div class="sidebar-nav-link-content">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Dashboard</span>
            </div>
        </a>
        <a href="calendar.php" class="sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'calendar.php' ? 'active' : ''; ?>">
            <div class="sidebar-nav-link-content">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Calendar</span>
            </div>
        </a>
        <a href="profile.php" class="sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            <div class="sidebar-nav-link-content">
                <i class="fa-solid fa-user-gear"></i>
                <span>Profile</span>
            </div>
        </a>
        <a href="manage_booking.php" class="sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_booking.php' ? 'active' : ''; ?>">
            <div class="sidebar-nav-link-content">
                <i class="fa-solid fa-book"></i>
                <span>Bookings</span>
            </div>
        </a>
        <a href="manage_court.php" class="sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_court.php' ? 'active' : ''; ?>">
            <div class="sidebar-nav-link-content">
                <i class="fa-solid fa-table-tennis-paddle-ball"></i>
                <span>Courts</span>
            </div>
        </a>
        <a href="manage_users.php" class="sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : ''; ?>">
            <div class="sidebar-nav-link-content">
                <i class="fa-solid fa-users"></i>
                <span>Users</span>
            </div>
        </a>

        <div class="menu-label mt-3">Sokongan (Support)</div>
        <a href="message.php" class="sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'message.php' ? 'active' : ''; ?>">
            <div class="sidebar-nav-link-content">
                <i class="fa-solid fa-comments"></i>
                <span>Message</span>
            </div>
        </a>
        <a href="manage_payment.php" class="sidebar-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_payment.php' ? 'active' : ''; ?>">
            <div class="sidebar-nav-link-content">
                <i class="fa-solid fa-file-invoice-dollar"></i>
                <span>Invoice / Payments</span>
            </div>
        </a>
    </div>
</div>