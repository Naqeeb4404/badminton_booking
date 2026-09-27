<?php
// Shared Admin Sidebar
// This file is included by every page in /admin so the layout stays identical.

$current_page = basename($_SERVER['PHP_SELF']);

$current_admin = $_SESSION['user'] ?? [
    'name' => 'Admin',
    'phone' => 'Admin Panel',
    'profile_pic' => ''
];

// =====================================================
// AMBIL DATA ADMIN TERBARU
// =====================================================
if (isset($conn) && !empty($_SESSION['user']['id'])) {

    $sidebar_admin_id = (int) $_SESSION['user']['id'];

    $sidebar_stmt = mysqli_prepare(
        $conn,
        "SELECT id, name, email, phone, role, profile_pic
         FROM users
         WHERE id = ?
         LIMIT 1"
    );

    if ($sidebar_stmt) {

        mysqli_stmt_bind_param(
            $sidebar_stmt,
            "i",
            $sidebar_admin_id
        );

        mysqli_stmt_execute($sidebar_stmt);

        $sidebar_result = mysqli_stmt_get_result($sidebar_stmt);

        $fresh_admin = $sidebar_result
            ? mysqli_fetch_assoc($sidebar_result)
            : null;

        mysqli_stmt_close($sidebar_stmt);

        if ($fresh_admin) {

            $current_admin = $fresh_admin;

            $_SESSION['user']['name'] =
                $fresh_admin['name'];

            $_SESSION['user']['email'] =
                $fresh_admin['email'];

            $_SESSION['user']['phone'] =
                $fresh_admin['phone'];

            $_SESSION['user']['role'] =
                $fresh_admin['role'];

            $_SESSION['user']['profile_pic'] =
                $fresh_admin['profile_pic'];
        }
    }
}


// =====================================================
// ADMIN PROFILE PICTURE
// =====================================================

$admin_photo = trim(
    (string)($current_admin['profile_pic'] ?? '')
);

$admin_photo_url = '';

if ($admin_photo !== '') {

    $admin_photo_path =
        __DIR__ . '/../uploads/' . basename($admin_photo);

    $admin_photo_version =
        is_file($admin_photo_path)
            ? (string) filemtime($admin_photo_path)
            : (string) time();

    $admin_photo_url =
        '../uploads/' .
        rawurlencode(basename($admin_photo)) .
        '?v=' .
        $admin_photo_version;
}

$admin_photo_style =
    $admin_photo_url !== ''
        ? "background-image:url('" .
          htmlspecialchars(
              $admin_photo_url,
              ENT_QUOTES,
              'UTF-8'
          ) .
          "'); background-size:cover;
          background-position:center;
          background-repeat:no-repeat;"
        : '';

$admin_initial = strtoupper(
    substr(
        (string)($current_admin['name'] ?? 'A'),
        0,
        1
    )
);


// =====================================================
// MESSAGE NOTIFICATION
// =====================================================

$unread_messages = 0;

if (isset($conn)) {

    $unread_query = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM messages
         WHERE is_read = 0"
    );

    if ($unread_query) {

        $unread_data =
            mysqli_fetch_assoc($unread_query);

        $unread_messages =
            (int)($unread_data['total'] ?? 0);
    }
}


// =====================================================
// MENU UTAMA
// =====================================================

$admin_sidebar_items = [

    [
        'file' => 'dashboard.php',
        'icon' => 'fa-chart-pie',
        'label' => 'Dashboard'
    ],

    [
        'file' => 'calendar.php',
        'icon' => 'fa-calendar-days',
        'label' => 'Calendar'
    ],

    [
        'file' => 'profile.php',
        'icon' => 'fa-user-gear',
        'label' => 'Profile'
    ],

    [
        'file' => 'manage_booking.php',
        'icon' => 'fa-book',
        'label' => 'Bookings'
    ],

    [
        'file' => 'manage_court.php',
        'icon' => 'fa-table-tennis-paddle-ball',
        'label' => 'Courts'
    ],

    [
        'file' => 'manage_users.php',
        'icon' => 'fa-users',
        'label' => 'Users'
    ],
];

?>


<div class="sidebar">

    <!-- =================================================
         PROFIL ADMIN
    ================================================== -->

    <div class="sidebar-profile-card">

        <div class="sidebar-profile-main">

            <div class="sidebar-avatar-wrap">

                <div
                    class="sidebar-avatar"
                    style="<?php echo $admin_photo_style; ?>"
                >
                    <?php
                    echo $admin_photo === ''
                        ? htmlspecialchars(
                            $admin_initial,
                            ENT_QUOTES,
                            'UTF-8'
                        )
                        : '';
                    ?>
                </div>

                <span class="sidebar-online-dot"></span>

            </div>


            <div class="sidebar-profile-text">

                <div class="sidebar-profile-name">

                    <?php
                    echo htmlspecialchars(
                        $current_admin['name']
                            ?? 'Admin',
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>

                </div>


                <div class="sidebar-profile-role">

                    <i class="fa-solid fa-shield-halved"></i>

                    Super Admin

                </div>

            </div>

        </div>


        <a
            href="profile.php"
            class="sidebar-settings"
            title="Admin Profile"
        >

            <i class="fa-solid fa-gear"></i>

        </a>

    </div>



    <!-- =================================================
         SIDEBAR MENU
    ================================================== -->

    <div class="sidebar-menu">


        <!-- MENU UTAMA -->

        <div class="menu-label">

            Menu Utama

        </div>


        <?php foreach ($admin_sidebar_items as $item): ?>

            <a
                href="<?php
                    echo htmlspecialchars(
                        $item['file']
                    );
                ?>"

                class="sidebar-nav-link<?php
                    echo $current_page === $item['file']
                        ? ' active'
                        : '';
                ?>"
            >

                <div class="sidebar-nav-link-content">

                    <i
                        class="fa-solid <?php
                            echo htmlspecialchars(
                                $item['icon']
                            );
                        ?>"
                    ></i>

                    <span>

                        <?php
                        echo htmlspecialchars(
                            $item['label']
                        );
                        ?>

                    </span>

                </div>

            </a>

        <?php endforeach; ?>



        <!-- =================================================
             SUPPORT
        ================================================== -->

        <div class="menu-label support-label">

            Sokongan (Support)

        </div>



        <!-- =================================================
             MESSAGE + NOTIFICATION
        ================================================== -->

        <a
            href="message.php"

            class="sidebar-nav-link<?php
                echo $current_page === 'message.php'
                    ? ' active'
                    : '';
            ?>"
        >

            <div class="sidebar-nav-link-content">

                <i class="fa-solid fa-comments"></i>

                <span>Message</span>

            </div>


            <?php if ($unread_messages > 0): ?>

                <span class="message-notification">

                    <?php

                    echo $unread_messages > 99
                        ? '99+'
                        : $unread_messages;

                    ?>

                </span>

            <?php endif; ?>

        </a>



        <!-- =================================================
             PAYMENT
        ================================================== -->

        <a
            href="manage_payment.php"

            class="sidebar-nav-link<?php
                echo $current_page === 'manage_payment.php'
                    ? ' active'
                    : '';
            ?>"
        >

            <div class="sidebar-nav-link-content">

                <i class="fa-solid fa-file-invoice-dollar"></i>

                <span>Invoice / Payments</span>

            </div>

        </a>



        <!-- =================================================
             REVENUE REPORT
        ================================================== -->

        <a
            href="monthly_revenue.php"

            class="sidebar-nav-link<?php
                echo $current_page === 'monthly_revenue.php'
                    ? ' active'
                    : '';
            ?>"
        >

            <div class="sidebar-nav-link-content">

                <i class="fa-solid fa-chart-line"></i>

                <span>Revenue Report</span>

            </div>

        </a>



        <!-- =================================================
             DAILY REPORT
        ================================================== -->

        <a
            href="daily_report.php"

            class="sidebar-nav-link<?php
                echo $current_page === 'daily_report.php'
                    ? ' active'
                    : '';
            ?>"
        >

            <div class="sidebar-nav-link-content">

                <i class="fa-solid fa-calendar-day"></i>

                <span>Daily Report</span>

            </div>

        </a>

    </div>

</div>


<!-- =====================================================
     MESSAGE NOTIFICATION STYLE
===================================================== -->

<style>

.message-notification {

    min-width: 21px;
    height: 21px;

    padding: 0 6px;

    display: flex;
    align-items: center;
    justify-content: center;

    margin-left: auto;

    background: #ef4444;
    color: #ffffff;

    border-radius: 999px;

    font-size: 11px;
    font-weight: 800;
    line-height: 1;

    box-shadow:
        0 0 0 3px rgba(239, 68, 68, 0.12);

    animation:
        messageNotificationPop 0.25s ease;
}


@keyframes messageNotificationPop {

    from {
        transform: scale(0.5);
        opacity: 0;
    }

    to {
        transform: scale(1);
        opacity: 1;
    }

}

</style>