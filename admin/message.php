<?php
session_start();

if (
    !isset($_SESSION['user']) ||
    ($_SESSION['user']['role'] ?? '') !== 'admin'
) {
    header("Location: ../auth/login.php");
    exit();
}

include __DIR__ . '/../config/db.php';

if (!isset($conn) || !$conn) {
    die("Ralat: Sambungan ke pangkalan data gagal.");
}

date_default_timezone_set('Asia/Kuala_Lumpur');

$user = $_SESSION['user'];

/* =====================================================
   REPLY MESSAGE
===================================================== */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['reply_message'])
) {
    $message_id = (int)($_POST['message_id'] ?? 0);
    $admin_reply = trim($_POST['admin_reply'] ?? '');

    if ($message_id > 0 && $admin_reply !== '') {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE messages
             SET admin_reply = ?,
                 replied_at = NOW()
             WHERE id = ?"
        );

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $admin_reply,
                $message_id
            );

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        header("Location: message.php?reply=success");
        exit();
    }
}

/* =====================================================
   DELETE MESSAGE
===================================================== */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_message'])
) {
    $message_id = (int)($_POST['message_id'] ?? 0);

    if ($message_id > 0) {

        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM messages WHERE id = ?"
        );

        if ($stmt) {
            mysqli_stmt_bind_param(
                $stmt,
                "i",
                $message_id
            );

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    header("Location: message.php?delete=success");
    exit();
}

/* =====================================================
   MARK ALL MESSAGE AS READ
===================================================== */
mysqli_query(
    $conn,
    "UPDATE messages
     SET is_read = 1
     WHERE is_read = 0"
);

/* =====================================================
   SEARCH
===================================================== */
$searchTerm = trim($_GET['search'] ?? '');

if ($searchTerm !== '') {

    $safeSearch = mysqli_real_escape_string(
        $conn,
        $searchTerm
    );

    $query = "
        SELECT *
        FROM messages
        WHERE
            name LIKE '%$safeSearch%'
            OR email LIKE '%$safeSearch%'
            OR message LIKE '%$safeSearch%'
            OR admin_reply LIKE '%$safeSearch%'
        ORDER BY id DESC
    ";

} else {

    $query = "
        SELECT *
        FROM messages
        ORDER BY id DESC
    ";
}

$result = mysqli_query($conn, $query);

if (!$result) {
    die(
        "Ralat query message: " .
        htmlspecialchars(mysqli_error($conn))
    );
}
?>
<!DOCTYPE html>
<html lang="ms">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Mesej & Pertanyaan - Admin Dashboard</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
>

<link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet"
>

<link
    rel="stylesheet"
    href="sidebar.css?v=20260927"
>

<style>

:root {
    --sidebar-bg: #07101f;
    --sidebar-text: #dee4ee;
    --body-bg: #07111f;
    --panel-bg: #0d1a2d;
    --border-color: #19345c;
    --text-main: #f8fafc;
    --text-muted: #94a3b8;
    --blue: #3b82f6;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    font-family: 'Plus Jakarta Sans', sans-serif;

    background:
        radial-gradient(
            circle at top right,
            rgba(6, 182, 212, 0.08),
            transparent 35%
        ),
        #07111f;

    color: var(--text-main);
}

/* =====================================================
   MAIN
===================================================== */

.main-content {
    margin-left: 350px;
    min-height: 100vh;
}

/* =====================================================
   TOPBAR
===================================================== */

.topbar {
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 42px;

    background: rgba(7, 17, 31, 0.90);

    border-bottom:
        1px solid rgba(148, 163, 184, 0.10);

    position: sticky;
    top: 0;
    z-index: 50;

    backdrop-filter: blur(12px);
}

/* =====================================================
   SEARCH
===================================================== */

.search-form {
    position: relative;
    width: 425px;
}

.search-input {
    width: 100%;
    height: 54px;

    padding:
        0
        20px
        0
        55px;

    background: #111d31;

    border:
        1px solid #27364d;

    border-radius: 13px;

    outline: none;

    color: #ffffff;

    font-size: 15px;

    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}

.search-input::placeholder {
    color: #7c8aa3;
}

.search-input:focus {
    border-color: #3b82f6;

    box-shadow:
        0 0 0 3px
        rgba(59, 130, 246, 0.12);
}

.search-form > i {
    position: absolute;

    left: 20px;
    top: 50%;

    transform: translateY(-50%);

    color: #7f8eaa;

    font-size: 18px;

    pointer-events: none;
}

/* =====================================================
   ADMIN
===================================================== */

.topbar-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.user-pill {
    display: flex;
    align-items: center;

    gap: 12px;

    padding:
        6px
        18px
        6px
        6px;

    background: #111d31;

    border:
        1px solid #263750;

    border-radius: 999px;
}

.user-avatar {
    width: 45px;
    height: 45px;

    border-radius: 50%;

    background: #07111f;

    border:
        2px solid #2563eb;

    display: flex;
    align-items: center;
    justify-content: center;

    background-size: cover;
    background-position: center;

    font-weight: 800;

    color: #ffffff;
}

.admin-name {
    color: #ffffff;

    font-size: 16px;

    font-weight: 800;
}

.logout-btn {
    display: flex;
    align-items: center;

    gap: 8px;

    padding:
        11px
        22px;

    border-radius: 14px;

    background: #dc2626;

    color: #ffffff;

    text-decoration: none;

    font-size: 14px;

    font-weight: 800;

    transition:
        transform 0.15s ease,
        background 0.15s ease;
}

.logout-btn:hover {
    color: #ffffff;

    background: #ef4444;

    transform: translateY(-1px);
}

/* =====================================================
   CONTENT
===================================================== */

.content-body {
    padding:
        38px
        44px;
}

.page-title {
    display: flex;
    align-items: center;

    gap: 14px;

    margin-bottom: 28px;
}

.page-title i {
    color: #60a5fa;

    font-size: 36px;
}

.page-title h1 {
    margin: 0;

    color: #ffffff;

    font-size: 36px;

    font-weight: 800;

    letter-spacing: -1px;
}

/* =====================================================
   ALERT
===================================================== */

.custom-alert {
    padding: 15px 18px;

    border-radius: 14px;

    margin-bottom: 22px;

    font-weight: 700;
}

.alert-success-custom {
    color: #86efac;

    background:
        rgba(34, 197, 94, 0.10);

    border:
        1px solid rgba(34, 197, 94, 0.25);
}

.alert-delete-custom {
    color: #fca5a5;

    background:
        rgba(239, 68, 68, 0.10);

    border:
        1px solid rgba(239, 68, 68, 0.25);
}

/* =====================================================
   CARD
===================================================== */

.message-card {
    background:
        rgba(13, 26, 45, 0.94);

    border:
        1px solid #19345c;

    border-radius: 24px;

    padding: 30px;

    box-shadow:
        0
        18px
        50px
        rgba(0, 0, 0, 0.16);
}

/* =====================================================
   TABLE
===================================================== */

.message-table {
    width: 100%;

    border-collapse: collapse;

    color: #ffffff;
}

.message-table thead {
    background: #26344d;
}

.message-table th {
    padding:
        15px
        12px;

    color: #9fb4de;

    font-size: 13px;

    font-weight: 800;

    text-transform: uppercase;

    letter-spacing: 0.2px;

    border: 0;
}

.message-table th:first-child {
    border-radius:
        10px
        0
        0
        0;
}

.message-table th:last-child {
    border-radius:
        0
        10px
        0
        0;
}

.message-table td {
    padding:
        18px
        12px;

    border-bottom:
        1px solid
        rgba(148, 163, 184, 0.15);

    color: #e7edf8;

    vertical-align: middle;

    font-size: 15px;
}

.message-table tbody tr {
    transition:
        background 0.15s ease;
}

.message-table tbody tr:hover {
    background:
        rgba(59, 130, 246, 0.05);
}

.message-name {
    font-weight: 800;

    color: #ffffff;
}

.email-text {
    display: block;

    margin-top: 4px;

    color: #7f8eaa;

    font-size: 12px;

    font-weight: 500;
}

.message-text {
    max-width: 390px;

    white-space: normal;

    word-break: break-word;

    line-height: 1.6;
}

.message-date {
    color: #c5d0e2;

    white-space: nowrap;
}

/* =====================================================
   ADMIN REPLY PREVIEW
===================================================== */

.reply-preview {
    margin-top: 10px;

    padding:
        10px
        12px;

    background:
        rgba(59, 130, 246, 0.08);

    border:
        1px solid
        rgba(59, 130, 246, 0.18);

    border-radius: 10px;

    color: #cbd5e1;

    font-size: 12px;

    line-height: 1.5;

    text-align: left;

    max-width: 260px;

    margin-left: auto;
}

.reply-preview strong {
    color: #60a5fa;
}

/* =====================================================
   ACTION BUTTONS
===================================================== */

.action-buttons {
    display: flex;

    align-items: center;

    justify-content: flex-end;

    gap: 8px;
}

.reply-btn,
.delete-btn {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    border: 0;

    color: #ffffff;

    font-size: 13px;

    font-weight: 800;

    cursor: pointer;

    white-space: nowrap;
}

.reply-btn {
    padding:
        9px
        18px;

    border-radius: 13px;

    background:
        linear-gradient(
            135deg,
            #3b82f6,
            #2563eb
        );

    box-shadow:
        0
        8px
        20px
        rgba(37, 99, 235, 0.20);
}

.reply-btn:hover {
    filter: brightness(1.08);

    transform: translateY(-1px);
}

.delete-btn {
    padding:
        9px
        15px;

    border-radius: 13px;

    background:
        linear-gradient(
            135deg,
            #ef4444,
            #dc2626
        );
}

.delete-btn:hover {
    filter: brightness(1.08);

    transform: translateY(-1px);
}

/* =====================================================
   EMPTY
===================================================== */

.empty-message {
    padding: 45px !important;

    text-align: center;

    color: #94a3b8 !important;
}

.empty-message i {
    display: block;

    margin-bottom: 12px;

    font-size: 35px;

    color: #64748b;
}

/* =====================================================
   REPLY MODAL
===================================================== */

.reply-modal {
    display: none;

    position: fixed;

    inset: 0;

    z-index: 99999;

    align-items: center;

    justify-content: center;

    padding: 20px;

    background:
        rgba(0, 0, 0, 0.72);

    backdrop-filter: blur(5px);
}

.reply-modal-box {
    width: 100%;

    max-width: 560px;

    padding: 26px;

    background: #0d1a2d;

    border:
        1px solid #24456f;

    border-radius: 22px;

    box-shadow:
        0
        30px
        80px
        rgba(0, 0, 0, 0.45);
}

.reply-modal-header {
    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    margin-bottom: 15px;
}

.reply-modal-header h3 {
    margin: 0;

    color: #ffffff;

    font-size: 21px;

    font-weight: 800;
}

.reply-modal-header h3 i {
    color: #60a5fa;

    margin-right: 7px;
}

.close-reply {
    width: 38px;

    height: 38px;

    display: flex;

    align-items: center;

    justify-content: center;

    border: 0;

    border-radius: 10px;

    background: #17263c;

    color: #94a3b8;

    font-size: 25px;

    cursor: pointer;
}

.close-reply:hover {
    color: #ffffff;

    background: #253750;
}

.reply-to {
    margin-bottom: 13px;

    color: #94a3b8;

    font-size: 14px;

    font-weight: 700;
}

.reply-modal textarea {
    width: 100%;

    min-height: 170px;

    padding: 15px;

    resize: vertical;

    outline: none;

    border:
        1px solid #334155;

    border-radius: 13px;

    background: #111d31;

    color: #ffffff;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    font-size: 14px;
}

.reply-modal textarea::placeholder {
    color: #64748b;
}

.reply-modal textarea:focus {
    border-color: #3b82f6;

    box-shadow:
        0 0 0 3px
        rgba(59, 130, 246, 0.12);
}

.send-reply-btn {
    width: 100%;

    margin-top: 15px;

    padding:
        13px
        18px;

    border: 0;

    border-radius: 13px;

    background:
        linear-gradient(
            135deg,
            #3b82f6,
            #2563eb
        );

    color: #ffffff;

    font-weight: 800;

    cursor: pointer;
}

.send-reply-btn:hover {
    filter: brightness(1.08);
}

/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 1100px) {

    .main-content {
        margin-left: 280px;
    }

    .search-form {
        width: 300px;
    }
}

@media (max-width: 768px) {

    .main-content {
        margin-left: 70px;
    }

    .topbar {
        padding:
            0
            18px;
    }

    .search-form {
        display: none;
    }

    .admin-name {
        display: none;
    }

    .user-pill {
        padding: 5px;
    }

    .content-body {
        padding:
            25px
            18px;
    }

    .page-title h1 {
        font-size: 25px;
    }

    .message-card {
        padding: 15px;
    }

    .action-buttons {
        flex-direction: column;
    }
}

</style>

</head>

<body class="admin-page">

<?php
include __DIR__ . '/sidebar.php';
?>

<div class="main-content">

    <!-- TOPBAR -->
    <header class="topbar">

        <form
            action=""
            method="GET"
            class="search-form"
        >

            <i
                class="fa-solid fa-magnifying-glass"
            ></i>

            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Cari mesej..."
                autocomplete="off"
                value="<?php
                    echo htmlspecialchars(
                        $searchTerm,
                        ENT_QUOTES,
                        'UTF-8'
                    );
                ?>"
            >

        </form>

        <div class="topbar-right">

            <div class="user-pill">

                <div
                    class="user-avatar"
                    style="<?php
                        echo $admin_photo_style ?? '';
                    ?>"
                >
                    <?php
                    echo empty($admin_photo)
                        ? htmlspecialchars(
                            $admin_initial ?? 'A',
                            ENT_QUOTES,
                            'UTF-8'
                        )
                        : '';
                    ?>
                </div>

                <div class="admin-name">
                    <?php
                    echo htmlspecialchars(
                        $current_admin['name']
                            ?? $user['name']
                            ?? 'Admin',
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?>
                </div>

            </div>

            <a
                href="../auth/logout.php"
                class="logout-btn"
            >
                <i
                    class="fa-solid fa-right-from-bracket"
                ></i>

                Log Keluar
            </a>

        </div>

    </header>


    <!-- CONTENT -->
    <main class="content-body">

        <div class="page-title">

            <i
                class="fa-solid fa-comments"
            ></i>

            <h1>
                Senarai Mesej & Pertanyaan
            </h1>

        </div>


        <!-- SUCCESS REPLY -->
        <?php if (
            isset($_GET['reply']) &&
            $_GET['reply'] === 'success'
        ): ?>

            <div
                class="
                    custom-alert
                    alert-success-custom
                "
            >
                <i
                    class="fa-solid fa-circle-check me-2"
                ></i>

                Balasan berjaya dihantar kepada user.
            </div>

        <?php endif; ?>


        <!-- SUCCESS DELETE -->
        <?php if (
            isset($_GET['delete']) &&
            $_GET['delete'] === 'success'
        ): ?>

            <div
                class="
                    custom-alert
                    alert-delete-custom
                "
            >
                <i
                    class="fa-solid fa-trash me-2"
                ></i>

                Mesej berjaya dipadam.
            </div>

        <?php endif; ?>


        <div class="message-card">

            <div class="table-responsive">

                <table class="message-table">

                    <thead>

                        <tr>

                            <th>
                                ID
                            </th>

                            <th>
                                Nama Pengirim
                            </th>

                            <th>
                                Mesej
                            </th>

                            <th>
                                Tarikh
                            </th>

                            <th class="text-end">
                                Tindakan
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php
                    if (
                        $result &&
                        mysqli_num_rows($result) > 0
                    ):
                    ?>

                        <?php
                        while (
                            $row =
                                mysqli_fetch_assoc(
                                    $result
                                )
                        ):
                        ?>

                            <tr>

                                <!-- ID -->
                                <td>
                                    #<?php
                                    echo (int)$row['id'];
                                    ?>
                                </td>


                                <!-- USER -->
                                <td
                                    class="message-name"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $row['name']
                                            ?? '-',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>

                                    <?php if (
                                        !empty(
                                            $row['email']
                                        )
                                    ): ?>

                                        <span
                                            class="email-text"
                                        >
                                            <?php
                                            echo htmlspecialchars(
                                                $row['email'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            );
                                            ?>
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- MESSAGE -->
                                <td
                                    class="message-text"
                                >

                                    <?php
                                    echo nl2br(
                                        htmlspecialchars(
                                            $row['message']
                                                ?? '',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        )
                                    );
                                    ?>

                                </td>


                                <!-- DATE -->
                                <td
                                    class="message-date"
                                >

                                    <?php
                                    echo !empty(
                                        $row['created_at']
                                    )
                                        ? htmlspecialchars(
                                            $row['created_at'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        )
                                        : '-';
                                    ?>

                                </td>


                                <!-- ACTION -->
                                <td class="text-end">

                                    <div
                                        class="action-buttons"
                                    >

                                        <!-- REPLY -->
                                        <button
                                            type="button"
                                            class="reply-btn"
                                            onclick='openReply(
                                                <?php
                                                echo (int)$row['id'];
                                                ?>,
                                                <?php
                                                echo json_encode(
                                                    $row['name']
                                                        ?? 'User'
                                                );
                                                ?>,
                                                <?php
                                                echo json_encode(
                                                    $row['admin_reply']
                                                        ?? ''
                                                );
                                                ?>
                                            )'
                                        >

                                            <i
                                                class="fa-solid fa-reply"
                                            ></i>

                                            Balas

                                        </button>


                                        <!-- DELETE -->
                                        <form
                                            method="POST"
                                            style="display:inline;"
                                            onsubmit="
                                                return confirm(
                                                    'Adakah anda pasti mahu delete mesej ini?'
                                                );
                                            "
                                        >

                                            <input
                                                type="hidden"
                                                name="message_id"
                                                value="<?php
                                                    echo (int)$row['id'];
                                                ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="delete_message"
                                                class="delete-btn"
                                            >

                                                <i
                                                    class="fa-solid fa-trash"
                                                ></i>

                                                Delete

                                            </button>

                                        </form>

                                    </div>


                                    <!-- EXISTING REPLY -->
                                    <?php if (
                                        !empty(
                                            $row['admin_reply']
                                        )
                                    ): ?>

                                        <div
                                            class="reply-preview"
                                        >

                                            <strong>
                                                Admin Reply:
                                            </strong>

                                            <br>

                                            <?php
                                            echo nl2br(
                                                htmlspecialchars(
                                                    $row['admin_reply'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                )
                                            );
                                            ?>

                                            <?php if (
                                                !empty(
                                                    $row['replied_at']
                                                )
                                            ): ?>

                                                <div
                                                    style="
                                                        margin-top:6px;
                                                        color:#64748b;
                                                    "
                                                >
                                                    <?php
                                                    echo htmlspecialchars(
                                                        $row['replied_at'],
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    );
                                                    ?>
                                                </div>

                                            <?php endif; ?>

                                        </div>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endwhile; ?>


                    <?php else: ?>

                        <tr>

                            <td
                                colspan="5"
                                class="empty-message"
                            >

                                <i
                                    class="fa-regular fa-message"
                                ></i>

                                Tiada mesej ditemui.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>


<!-- =====================================================
     REPLY MODAL
===================================================== -->

<div
    id="replyModal"
    class="reply-modal"
>

    <div class="reply-modal-box">

        <div class="reply-modal-header">

            <h3>

                <i
                    class="fa-solid fa-reply"
                ></i>

                Balas Message

            </h3>

            <button
                type="button"
                class="close-reply"
                onclick="closeReply()"
            >
                &times;
            </button>

        </div>


        <div
            class="reply-to"
            id="replyUser"
        ></div>


        <form method="POST">

            <input
                type="hidden"
                name="message_id"
                id="replyMessageId"
            >

            <textarea
                name="admin_reply"
                id="adminReply"
                placeholder="Tulis balasan kepada user..."
                required
            ></textarea>

            <button
                type="submit"
                name="reply_message"
                class="send-reply-btn"
            >

                <i
                    class="fa-solid fa-paper-plane me-1"
                ></i>

                Hantar Balasan

            </button>

        </form>

    </div>

</div>


<script>

function openReply(
    id,
    name,
    currentReply
) {

    document
        .getElementById(
            'replyMessageId'
        )
        .value = id;

    document
        .getElementById(
            'replyUser'
        )
        .textContent =
            'Balas kepada: ' + name;

    document
        .getElementById(
            'adminReply'
        )
        .value =
            currentReply || '';

    document
        .getElementById(
            'replyModal'
        )
        .style.display =
            'flex';

}


function closeReply() {

    document
        .getElementById(
            'replyModal'
        )
        .style.display =
            'none';

}


document
    .getElementById(
        'replyModal'
    )
    .addEventListener(
        'click',
        function (event) {

            if (
                event.target === this
            ) {
                closeReply();
            }

        }
    );


document.addEventListener(
    'keydown',
    function (event) {

        if (
            event.key === 'Escape'
        ) {
            closeReply();
        }

    }
);

</script>

</body>
</html>