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
    die("Ralat: Sambungan ke database gagal.");
}

date_default_timezone_set('Asia/Kuala_Lumpur');

$user = $_SESSION['user'];

/* =====================================================
   ADMIN REPLY
===================================================== */
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['reply_message'])
) {
    $message_id = (int)($_POST['message_id'] ?? 0);
    $admin_reply = trim($_POST['admin_reply'] ?? '');

    if ($message_id > 0 && $admin_reply !== '') {

        $stmt = $conn->prepare("
            UPDATE messages
            SET
                admin_reply = ?,
                replied_at = NOW(),
                reply_is_read = 0
            WHERE id = ?
        ");

        if ($stmt) {
            $stmt->bind_param(
                "si",
                $admin_reply,
                $message_id
            );

            $stmt->execute();
            $stmt->close();
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

        $stmt = $conn->prepare("
            DELETE FROM messages
            WHERE id = ?
        ");

        if ($stmt) {
            $stmt->bind_param(
                "i",
                $message_id
            );

            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: message.php?delete=success");
    exit();
}

/* =====================================================
   MARK USER MESSAGE AS READ BY ADMIN
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

$result = mysqli_query(
    $conn,
    $query
);

if (!$result) {
    die(
        "Ralat query: " .
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

<title>
    Mesej & Pertanyaan - Admin Dashboard
</title>

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

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    background:
        radial-gradient(
            circle at top right,
            rgba(6,182,212,.08),
            transparent 35%
        ),
        #07111f;

    color: #f8fafc;
}

.main-content {
    margin-left: 350px;
    min-height: 100vh;
}

/* ================= TOPBAR ================= */

.topbar {
    height: 80px;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 42px;

    background:
        rgba(7,17,31,.90);

    border-bottom:
        1px solid
        rgba(148,163,184,.10);

    position: sticky;
    top: 0;

    z-index: 50;

    backdrop-filter:
        blur(12px);
}

/* ================= SEARCH ================= */

.search-form {
    position: relative;
    width: 425px;
}

.search-input {
    width: 100%;
    height: 54px;

    padding:
        0 20px 0 55px;

    background: #111d31;

    border:
        1px solid #27364d;

    border-radius: 13px;

    outline: none;

    color: #fff;

    font-size: 15px;
}

.search-input::placeholder {
    color: #7c8aa3;
}

.search-input:focus {
    border-color: #3b82f6;

    box-shadow:
        0 0 0 3px
        rgba(59,130,246,.12);
}

.search-form > i {
    position: absolute;

    left: 20px;
    top: 50%;

    transform:
        translateY(-50%);

    color: #7f8eaa;
}

/* ================= ADMIN ================= */

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
        6px 18px 6px 6px;

    background: #111d31;

    border:
        1px solid #263750;

    border-radius: 999px;
}

.user-avatar {
    width: 45px;
    height: 45px;

    border-radius: 50%;

    border:
        2px solid #2563eb;

    background: #07111f;

    display: flex;
    align-items: center;
    justify-content: center;

    background-size: cover;
    background-position: center;

    color: #fff;
    font-weight: 800;
}

.admin-name {
    color: #fff;
    font-weight: 800;
}

.logout-btn {
    display: flex;
    align-items: center;

    gap: 8px;

    padding:
        11px 22px;

    border-radius: 14px;

    background: #dc2626;

    color: #fff;

    text-decoration: none;

    font-weight: 800;
}

.logout-btn:hover {
    background: #ef4444;
    color: #fff;
}

/* ================= CONTENT ================= */

.content-body {
    padding:
        38px 44px;
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

    color: #fff;

    font-size: 36px;
    font-weight: 800;
}

/* ================= ALERT ================= */

.notice {
    padding: 14px 18px;

    margin-bottom: 20px;

    border-radius: 13px;

    font-weight: 700;
}

.notice-success {
    color: #86efac;

    background:
        rgba(34,197,94,.10);

    border:
        1px solid
        rgba(34,197,94,.25);
}

.notice-delete {
    color: #fca5a5;

    background:
        rgba(239,68,68,.10);

    border:
        1px solid
        rgba(239,68,68,.25);
}

/* ================= CARD ================= */

.message-card {
    background:
        rgba(13,26,45,.94);

    border:
        1px solid #19345c;

    border-radius: 24px;

    padding: 30px;
}

/* ================= TABLE ================= */

.message-table {
    width: 100%;

    border-collapse:
        collapse;

    color: #fff;
}

.message-table thead {
    background: #26344d;
}

.message-table th {
    padding:
        15px 12px;

    color: #9fb4de;

    font-size: 13px;

    font-weight: 800;

    text-transform:
        uppercase;
}

.message-table td {
    padding:
        18px 12px;

    border-bottom:
        1px solid
        rgba(148,163,184,.15);

    vertical-align:
        middle;
}

.message-name {
    font-weight: 800;
}

.email {
    display: block;

    margin-top: 4px;

    color: #7f8eaa;

    font-size: 12px;

    font-weight: 500;
}

.message-text {
    max-width: 400px;

    word-break:
        break-word;

    line-height: 1.6;
}

.message-date {
    color: #c5d0e2;

    white-space:
        nowrap;
}

/* ================= ACTION ================= */

.action-buttons {
    display: flex;

    justify-content:
        flex-end;

    gap: 8px;
}

.reply-btn,
.delete-btn {
    border: 0;

    border-radius: 12px;

    padding:
        9px 15px;

    color: #fff;

    font-size: 13px;

    font-weight: 800;

    cursor: pointer;
}

.reply-btn {
    background:
        linear-gradient(
            135deg,
            #3b82f6,
            #2563eb
        );
}

.delete-btn {
    background:
        linear-gradient(
            135deg,
            #ef4444,
            #dc2626
        );
}

.reply-preview {
    max-width: 280px;

    margin:
        12px 0 0 auto;

    padding: 11px;

    border-radius: 10px;

    background:
        rgba(59,130,246,.08);

    border:
        1px solid
        rgba(59,130,246,.20);

    color: #cbd5e1;

    font-size: 12px;

    text-align: left;
}

.reply-preview strong {
    color: #60a5fa;
}

/* ================= EMPTY ================= */

.empty-message {
    padding: 45px !important;

    text-align: center;

    color: #94a3b8 !important;
}

/* ================= MODAL ================= */

.reply-modal {
    display: none;

    position: fixed;

    inset: 0;

    z-index: 99999;

    align-items: center;
    justify-content: center;

    padding: 20px;

    background:
        rgba(0,0,0,.72);

    backdrop-filter:
        blur(5px);
}

.reply-modal-box {
    width: 100%;

    max-width: 560px;

    padding: 26px;

    background: #0d1a2d;

    border:
        1px solid #24456f;

    border-radius: 22px;
}

.reply-modal-header {
    display: flex;

    align-items: center;
    justify-content: space-between;

    margin-bottom: 15px;
}

.reply-modal-header h3 {
    margin: 0;

    font-size: 21px;

    font-weight: 800;
}

.close-reply {
    width: 38px;
    height: 38px;

    border: 0;

    border-radius: 10px;

    background: #17263c;

    color: #fff;

    font-size: 25px;
}

.reply-to {
    margin-bottom: 13px;

    color: #94a3b8;

    font-size: 14px;
}

.reply-modal textarea {
    width: 100%;

    min-height: 170px;

    padding: 15px;

    border:
        1px solid #334155;

    border-radius: 13px;

    outline: none;

    resize: vertical;

    background: #111d31;

    color: #fff;

    font-family:
        'Plus Jakarta Sans',
        sans-serif;
}

.send-reply-btn {
    width: 100%;

    margin-top: 15px;

    padding: 13px;

    border: 0;

    border-radius: 13px;

    background:
        linear-gradient(
            135deg,
            #3b82f6,
            #2563eb
        );

    color: #fff;

    font-weight: 800;
}

@media(max-width:1100px) {

    .main-content {
        margin-left: 280px;
    }

    .search-form {
        width: 300px;
    }
}

@media(max-width:768px) {

    .main-content {
        margin-left: 70px;
    }

    .search-form {
        display: none;
    }

    .content-body {
        padding: 25px 18px;
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

<header class="topbar">

    <form
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
            value="<?= htmlspecialchars(
                $searchTerm,
                ENT_QUOTES,
                'UTF-8'
            ) ?>"
        >

    </form>


    <div class="topbar-right">

        <div class="user-pill">

            <div
                class="user-avatar"
                style="<?= $admin_photo_style ?? '' ?>"
            >

                <?= empty($admin_photo)
                    ? htmlspecialchars(
                        $admin_initial ?? 'A'
                    )
                    : ''
                ?>

            </div>

            <div class="admin-name">

                <?= htmlspecialchars(
                    $current_admin['name']
                    ?? $user['name']
                    ?? 'Admin'
                ) ?>

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


<main class="content-body">

    <div class="page-title">

        <i
            class="fa-solid fa-comments"
        ></i>

        <h1>
            Senarai Mesej & Pertanyaan
        </h1>

    </div>


    <?php if (
        ($_GET['reply'] ?? '') === 'success'
    ): ?>

        <div
            class="
                notice
                notice-success
            "
        >
            <i
                class="fa-solid fa-circle-check"
            ></i>

            Balasan berjaya dihantar.
        </div>

    <?php endif; ?>


    <?php if (
        ($_GET['delete'] ?? '') === 'success'
    ): ?>

        <div
            class="
                notice
                notice-delete
            "
        >
            <i
                class="fa-solid fa-trash"
            ></i>

            Mesej berjaya dipadam.
        </div>

    <?php endif; ?>


    <div class="message-card">

        <div class="table-responsive">

            <table class="message-table">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>
                            Nama Pengirim
                        </th>

                        <th>Mesej</th>

                        <th>Tarikh</th>

                        <th class="text-end">
                            Tindakan
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php if (
                    mysqli_num_rows($result) > 0
                ): ?>

                    <?php while (
                        $row =
                            mysqli_fetch_assoc(
                                $result
                            )
                    ): ?>

                        <tr>

                            <td>
                                #<?= (int)$row['id'] ?>
                            </td>


                            <td
                                class="message-name"
                            >

                                <?= htmlspecialchars(
                                    $row['name']
                                    ?? '-'
                                ) ?>

                                <span class="email">

                                    <?= htmlspecialchars(
                                        $row['email']
                                        ?? ''
                                    ) ?>

                                </span>

                            </td>


                            <td
                                class="message-text"
                            >

                                <?= nl2br(
                                    htmlspecialchars(
                                        $row['message']
                                        ?? ''
                                    )
                                ) ?>

                            </td>


                            <td
                                class="message-date"
                            >

                                <?= htmlspecialchars(
                                    $row['created_at']
                                    ?? '-'
                                ) ?>

                            </td>


                            <td class="text-end">

                                <div
                                    class="action-buttons"
                                >

                                    <button
                                        type="button"
                                        class="reply-btn"
                                        onclick='openReply(
                                            <?= (int)$row['id'] ?>,
                                            <?= json_encode(
                                                $row['name']
                                                ?? 'User'
                                            ) ?>,
                                            <?= json_encode(
                                                $row['admin_reply']
                                                ?? ''
                                            ) ?>
                                        )'
                                    >

                                        <i
                                            class="fa-solid fa-reply"
                                        ></i>

                                        Balas

                                    </button>


                                    <form
                                        method="POST"
                                        onsubmit="
                                            return confirm(
                                                'Delete message ini?'
                                            );
                                        "
                                    >

                                        <input
                                            type="hidden"
                                            name="message_id"
                                            value="<?= (int)$row['id'] ?>"
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


                                <?php if (
                                    !empty(
                                        $row['admin_reply']
                                    )
                                ): ?>

                                    <div
                                        class="reply-preview"
                                    >

                                        <strong>
                                            Admin Reply
                                        </strong>

                                        <br>

                                        <?= nl2br(
                                            htmlspecialchars(
                                                $row['admin_reply']
                                            )
                                        ) ?>

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

                            <br>

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


<!-- REPLY MODAL -->

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
                    class="fa-solid fa-paper-plane"
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

    document.getElementById(
        'replyMessageId'
    ).value = id;

    document.getElementById(
        'replyUser'
    ).textContent =
        'Balas kepada: ' + name;

    document.getElementById(
        'adminReply'
    ).value =
        currentReply || '';

    document.getElementById(
        'replyModal'
    ).style.display =
        'flex';
}


function closeReply() {

    document.getElementById(
        'replyModal'
    ).style.display =
        'none';
}


document.getElementById(
    'replyModal'
).addEventListener(
    'click',
    function(event) {

        if (
            event.target === this
        ) {
            closeReply();
        }

    }
);

</script>

</body>
</html>