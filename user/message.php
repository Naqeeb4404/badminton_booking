<?php
session_start();

include __DIR__ . '/../config/db.php';

date_default_timezone_set(
    'Asia/Kuala_Lumpur'
);

if (!isset($_SESSION['user'])) {

    header(
        'Location: ../auth/login.php'
    );

    exit();
}

$user = $_SESSION['user'];

$user_id =
    (int)($user['id'] ?? 0);

$name =
    trim($user['name'] ?? '');

$email =
    trim($user['email'] ?? '');

$success = '';
$error = '';


/* =====================================================
   SEND MESSAGE
===================================================== */

if (
    $_SERVER['REQUEST_METHOD']
    === 'POST'
) {

    $email =
        trim(
            $_POST['email']
            ?? $email
        );

    $message =
        trim(
            $_POST['message']
            ?? ''
        );


    if (
        $name === '' ||
        $email === '' ||
        $message === ''
    ) {

        $error =
            'Please complete all fields.';

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            'Please enter a valid email address.';

    } else {

        $stmt =
            $conn->prepare(
                "
                INSERT INTO messages
                (
                    user_id,
                    name,
                    email,
                    message,
                    is_read,
                    reply_is_read,
                    created_at
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    0,
                    1,
                    NOW()
                )
                "
            );


        if ($stmt) {

            $stmt->bind_param(
                'isss',
                $user_id,
                $name,
                $email,
                $message
            );


            if (
                $stmt->execute()
            ) {

                $success =
                    'Your message has been sent to the admin.';

                $_POST['message'] = '';

            } else {

                $error =
                    'Message could not be sent. Please try again.';
            }


            $stmt->close();

        } else {

            $error =
                'Message could not be sent. Please check the messages table.';
        }
    }
}


/* =====================================================
   GET USER MESSAGES
===================================================== */

$stmt =
    $conn->prepare(
        "
        SELECT
            id,
            message,
            admin_reply,
            replied_at,
            created_at,
            reply_is_read
        FROM messages
        WHERE user_id = ?
        ORDER BY id DESC
        "
    );

$stmt->bind_param(
    'i',
    $user_id
);

$stmt->execute();

$messages =
    $stmt->get_result();


/* =====================================================
   MARK ADMIN REPLY AS READ
===================================================== */

$stmtRead =
    $conn->prepare(
        "
        UPDATE messages
        SET reply_is_read = 1
        WHERE user_id = ?
          AND admin_reply IS NOT NULL
          AND admin_reply != ''
          AND reply_is_read = 0
        "
    );

if ($stmtRead) {

    $stmtRead->bind_param(
        'i',
        $user_id
    );

    $stmtRead->execute();

    $stmtRead->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Message - Badminton Kampung Panji
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


<style>

:root {

    --bg: #080b10;

    --panel: #10151d;

    --border: #252b35;

    --text: #f5f7fb;

    --muted: #9ba8bb;
}


* {
    box-sizing: border-box;
}


body {

    margin: 0;

    background:
        var(--bg);

    color:
        var(--text);

    font-family:
        'Plus Jakarta Sans',
        sans-serif;

    min-height:
        100vh;
}


/* =====================================================
   TOPBAR
===================================================== */

.topbar {

    height: 82px;

    border-bottom:
        1px solid #1d222b;

    display: flex;

    align-items: center;

    justify-content:
        space-between;

    padding:
        0 4.5%;

    position: sticky;

    top: 0;

    background:
        #080b10;

    z-index: 10;
}


.brand {

    display: flex;

    align-items: center;

    gap: 14px;

    text-decoration: none;

    color: #fff;
}


.logo {

    width: 52px;

    height: 52px;

    border-radius: 14px;

    background:
        linear-gradient(
            135deg,
            #6658ff,
            #9c46f2
        );

    display: grid;

    place-items: center;

    font-size: 23px;
}


.brand b {
    display: block;
}


.brand small {

    display: block;

    color: #b65cff;

    text-transform:
        uppercase;

    letter-spacing:
        2px;

    font-weight: 800;
}


.nav {

    display: flex;

    align-items: center;

    gap: 30px;
}


.nav a {

    color: #a9b4c7;

    text-decoration: none;

    font-weight: 700;
}


.nav a.active,
.nav a:hover {

    color: #fff;
}


.logout {

    border:
        1px solid #68252b;

    color: #ff7077;

    padding:
        11px 22px;

    border-radius:
        999px;

    text-decoration:
        none;

    font-weight: 800;
}


/* =====================================================
   CONTENT
===================================================== */

.wrap {

    max-width: 900px;

    margin:
        55px auto;

    padding:
        0 22px;
}


.title {

    font-size: 42px;

    font-weight: 800;
}


.subtitle {

    color:
        var(--muted);

    margin-bottom:
        28px;
}


.cardx {

    background:
        var(--panel);

    border:
        1px solid
        var(--border);

    border-radius:
        24px;

    padding:
        30px;

    margin-bottom:
        25px;
}


/* =====================================================
   FORM
===================================================== */

.form-control {

    background:
        #0b0f15 !important;

    border:
        1px solid
        #2b3340 !important;

    color:
        #fff !important;

    border-radius:
        13px !important;

    padding:
        13px 15px !important;
}


.form-control:focus {

    box-shadow:
        0 0 0 3px
        rgba(139,92,246,.16)
        !important;

    border-color:
        #7557e8 !important;
}


textarea.form-control {

    min-height:
        150px;
}


.send {

    border: 0;

    background:
        linear-gradient(
            135deg,
            #6558ff,
            #9448ef
        );

    color: #fff;

    border-radius:
        13px;

    padding:
        13px 22px;

    font-weight: 800;
}


/* =====================================================
   MESSAGE HISTORY
===================================================== */

.history-title {

    font-size:
        25px;

    font-weight:
        800;

    margin:
        35px 0 18px;
}


.msg {

    background:
        #0d121a;

    border:
        1px solid
        #252b35;

    border-radius:
        18px;

    padding:
        20px;

    margin-bottom:
        15px;
}


.msg-head {

    display: flex;

    justify-content:
        space-between;

    gap: 15px;

    color:
        #94a3b8;

    font-size:
        13px;

    margin-bottom:
        10px;
}


.user-msg {

    line-height:
        1.6;
}


/* =====================================================
   ADMIN REPLY
===================================================== */

.admin-reply {

    margin-top:
        15px;

    background:
        rgba(
            139,
            92,
            246,
            .09
        );

    border:
        1px solid
        rgba(
            139,
            92,
            246,
            .30
        );

    border-radius:
        14px;

    padding:
        16px;
}


.admin-reply strong {

    color:
        #c4b5fd;
}


.waiting {

    margin-top:
        14px;

    color:
        #94a3b8;

    font-size:
        13px;
}


.back {

    color:
        #a9b4c7;

    text-decoration:
        none;

    font-weight:
        700;
}


@media(max-width:800px) {

    .nav {
        display: none;
    }

    .topbar {
        padding:
            0 20px;
    }

    .title {
        font-size:
            32px;
    }

    .cardx {
        padding:
            22px;
    }
}

</style>

</head>


<body>


<header class="topbar">


<a
    class="brand"
    href="dashboard.php"
>

    <div class="logo">

        <i
            class="fa-solid fa-feather"
        ></i>

    </div>


    <div>

        <b>
            BADMINTON
        </b>

        <small>
            Kampung Panji
        </small>

    </div>

</a>


<nav class="nav">

    <a
        href="feedback_report.php"
    >
        Feedback
    </a>


    <a
        href="message.php"
        class="active"
    >
        Message
    </a>


    <a
        href="my_booking.php"
    >
        My Booking
    </a>


    <a
        href="profile.php"
    >
        Profile
    </a>

</nav>


<a
    class="logout"
    href="../auth/logout.php"
>

    <i
        class="fa-solid fa-right-from-bracket me-1"
    ></i>

    Log Out

</a>


</header>


<main class="wrap">


<a
    class="back"
    href="dashboard.php"
>

    <i
        class="fa-solid fa-arrow-left me-2"
    ></i>

    Back to Dashboard

</a>


<h1 class="title mt-4">

    Message & Questions

</h1>


<p class="subtitle">

    Send your question to the admin
    and read the admin reply here.

</p>


<div class="cardx">


<?php if ($success): ?>

    <div
        class="alert alert-success"
    >

        <?= htmlspecialchars(
            $success
        ) ?>

    </div>

<?php endif; ?>


<?php if ($error): ?>

    <div
        class="alert alert-danger"
    >

        <?= htmlspecialchars(
            $error
        ) ?>

    </div>

<?php endif; ?>


<form method="POST">


<div class="mb-4">

    <label
        class="mb-2 fw-bold"
    >
        Name
    </label>

    <input
        class="form-control"
        value="<?= htmlspecialchars(
            $name
        ) ?>"
        readonly
    >

</div>


<div class="mb-4">

    <label
        class="mb-2 fw-bold"
    >
        Email
    </label>

    <input
        type="email"
        class="form-control"
        name="email"
        value="<?= htmlspecialchars(
            $email
        ) ?>"
        required
    >

</div>


<div class="mb-4">

    <label
        class="mb-2 fw-bold"
    >
        Your Message
    </label>

    <textarea
        class="form-control"
        name="message"
        placeholder="Type your question here..."
        required
    ><?= htmlspecialchars(
        $_POST['message']
        ?? ''
    ) ?></textarea>

</div>


<button class="send">

    <i
        class="fa-solid fa-paper-plane me-2"
    ></i>

    Send Message

</button>


</form>

</div>


<h2 class="history-title">

    <i
        class="fa-regular fa-comments me-2"
    ></i>

    My Messages

</h2>


<?php if (
    $messages &&
    $messages->num_rows
): ?>


    <?php while (
        $row =
            $messages->fetch_assoc()
    ): ?>


        <div class="msg">


            <div class="msg-head">

                <strong>
                    You
                </strong>

                <span>

                    <?= htmlspecialchars(
                        $row['created_at']
                    ) ?>

                </span>

            </div>


            <div class="user-msg">

                <?= nl2br(
                    htmlspecialchars(
                        $row['message']
                    )
                ) ?>

            </div>


            <?php if (
                !empty(
                    $row['admin_reply']
                )
            ): ?>


                <div class="admin-reply">

                    <strong>

                        <i
                            class="fa-solid fa-shield-halved me-1"
                        ></i>

                        Admin Reply

                    </strong>


                    <div class="mt-2">

                        <?= nl2br(
                            htmlspecialchars(
                                $row['admin_reply']
                            )
                        ) ?>

                    </div>


                    <?php if (
                        !empty(
                            $row['replied_at']
                        )
                    ): ?>

                        <small
                            class="
                                d-block
                                mt-2
                                text-secondary
                            "
                        >

                            <?= htmlspecialchars(
                                $row['replied_at']
                            ) ?>

                        </small>

                    <?php endif; ?>

                </div>


            <?php else: ?>


                <div class="waiting">

                    <i
                        class="fa-regular fa-clock me-1"
                    ></i>

                    Waiting for admin reply

                </div>


            <?php endif; ?>


        </div>


    <?php endwhile; ?>


<?php else: ?>


    <div
        class="
            msg
            text-secondary
        "
    >

        No messages yet.

    </div>


<?php endif; ?>


</main>


</body>

</html>