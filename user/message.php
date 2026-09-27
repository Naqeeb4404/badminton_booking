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

\===================================================== */



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

\===================================================== */



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

\===================================================== */



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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Messages - Badminton Kampung Panji</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#090a0f;--panel:#141620;--border:rgba(255,255,255,.09);--text:#f8fafc;--muted:#94a3b8;--indigo:#6366f1;--purple:#a855f7;--green:#4ade80;--yellow:#fbbf24}
*{box-sizing:border-box}
body{margin:0;min-height:100vh;font-family:'Plus Jakarta Sans',sans-serif;background:#090a0f;color:var(--text);overflow-x:hidden}
body:before{content:"";position:fixed;inset:0;z-index:-3;background:radial-gradient(circle at 8% 8%,rgba(99,102,241,.22),transparent 31%),radial-gradient(circle at 92% 75%,rgba(168,85,247,.16),transparent 31%),linear-gradient(180deg,#090a0f,#0d0e16 55%,#090a0f)}
.orb{position:fixed;border-radius:50%;filter:blur(130px);opacity:.13;z-index:-2;pointer-events:none}.orb.one{width:350px;height:350px;background:#6366f1;left:-160px;top:170px}.orb.two{width:390px;height:390px;background:#a855f7;right:-180px;bottom:-70px}
.navbar{height:78px;padding:10px 40px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);background:rgba(9,10,15,.84);backdrop-filter:blur(18px);position:sticky;top:0;z-index:20}
.brand{display:flex;align-items:center;gap:12px;text-decoration:none}.brand-logo{width:53px;height:53px;display:flex;align-items:center;justify-content:center;border-radius:10px;overflow:hidden}.brand-logo img{width:51px;height:51px;object-fit:contain;border-radius:9px}.brand-text{display:flex;flex-direction:column}.brand-text strong{font-size:.95rem;color:#fff;line-height:1.05}.brand-text span{font-size:.62rem;color:#a855f7;font-weight:800;letter-spacing:1.5px;margin-top:5px}
.nav-actions{display:flex;align-items:center;gap:8px}.nav-btn{height:40px;padding:0 14px;border:1px solid var(--border);border-radius:50px;background:rgba(255,255,255,.035);color:#dbe3ef;text-decoration:none;display:flex;align-items:center;gap:7px;font-size:.68rem;font-weight:800;transition:.2s}.nav-btn:hover{background:rgba(255,255,255,.08);color:#fff}.logout{border-color:rgba(251,113,133,.2);color:#fda4af;background:rgba(251,113,133,.06)}
.wrapper{width:min(1120px,calc(100% - 30px));margin:0 auto;padding:35px 0 65px}
.hero{position:relative;overflow:hidden;padding:28px 29px;border:1px solid var(--border);border-radius:25px;background:linear-gradient(135deg,rgba(25,27,41,.96),rgba(14,15,23,.94));box-shadow:0 22px 60px rgba(0,0,0,.28);margin-bottom:18px}.hero:after{content:"💬";position:absolute;right:25px;bottom:-34px;font-size:8rem;opacity:.055;transform:rotate(-12deg)}.eyebrow{display:inline-flex;align-items:center;gap:7px;padding:6px 11px;border-radius:50px;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.22);color:#a5b4fc;font-size:.59rem;font-weight:800;letter-spacing:1px;text-transform:uppercase}.hero h1{font-size:1.9rem;letter-spacing:-.9px;margin:11px 0 6px}.hero p{font-size:.72rem;color:#94a3b8;line-height:1.6;margin:0;max-width:600px}
.layout{display:grid;grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr);gap:16px;align-items:start}.panel{border:1px solid var(--border);border-radius:22px;background:linear-gradient(145deg,rgba(23,25,37,.96),rgba(14,15,23,.94));box-shadow:0 18px 48px rgba(0,0,0,.22);overflow:hidden}
.compose{padding:24px;position:sticky;top:95px}.panel-label{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:50px;background:rgba(168,85,247,.09);border:1px solid rgba(168,85,247,.18);color:#c4b5fd;font-size:.56rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px}.compose h2{font-size:1.12rem;margin:11px 0 5px}.compose-intro{font-size:.66rem;color:#94a3b8;line-height:1.55;margin:0 0 18px}.field{margin-bottom:13px}.field label{display:block;font-size:.59rem;color:#cbd5e1;font-weight:800;margin:0 0 6px}.input{width:100%;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:11px 12px;color:#fff;font-family:inherit;font-size:.68rem;outline:none;transition:.2s}.input:focus{border-color:#818cf8;background:rgba(99,102,241,.05);box-shadow:0 0 0 3px rgba(99,102,241,.08)}.input[readonly]{color:#94a3b8}.input::placeholder{color:#64748b}textarea.input{min-height:145px;resize:vertical}.send{width:100%;height:46px;border:0;border-radius:50px;background:linear-gradient(135deg,#6366f1,#a855f7);color:#fff;font-family:inherit;font-size:.71rem;font-weight:800;cursor:pointer;box-shadow:0 9px 25px rgba(99,102,241,.22);transition:.2s}.send:hover{transform:translateY(-2px);box-shadow:0 13px 30px rgba(99,102,241,.32)}.safe{display:flex;align-items:center;justify-content:center;gap:6px;color:#64748b;font-size:.54rem;margin-top:11px}.safe i{color:#4ade80}
.alert{padding:10px 12px;border-radius:11px;margin-bottom:13px;font-size:.64rem;font-weight:700}.success{background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.17);color:#86efac}.error{background:rgba(251,113,133,.08);border:1px solid rgba(251,113,133,.17);color:#fda4af}
.inbox{padding:23px}.inbox-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:15px}.inbox-title{display:flex;align-items:center;gap:10px}.inbox-icon{width:37px;height:37px;border-radius:11px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(99,102,241,.18),rgba(168,85,247,.13));border:1px solid rgba(99,102,241,.18);color:#a5b4fc}.inbox-title h2{font-size:.91rem;margin:0}.inbox-title small{display:block;color:#64748b;font-size:.54rem;margin-top:2px}.support-badge{padding:6px 9px;border-radius:50px;background:rgba(74,222,128,.07);border:1px solid rgba(74,222,128,.15);color:#86efac;font-size:.52rem;font-weight:800}
.messages{display:flex;flex-direction:column;gap:11px;max-height:670px;overflow-y:auto;padding-right:3px}.messages::-webkit-scrollbar{width:5px}.messages::-webkit-scrollbar-thumb{background:rgba(255,255,255,.12);border-radius:20px}.thread{border:1px solid rgba(255,255,255,.075);border-radius:16px;padding:14px;background:rgba(255,255,255,.024);transition:.2s}.thread:hover{border-color:rgba(99,102,241,.2);background:rgba(99,102,241,.03)}.thread-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px}.who{display:flex;align-items:center;gap:9px}.avatar{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#4f46e5,#9333ea);font-size:.7rem;font-weight:800}.who strong{display:block;font-size:.66rem}.who small{display:block;font-size:.5rem;color:#64748b;margin-top:2px}.date{font-size:.5rem;color:#64748b;white-space:nowrap}.bubble{padding:10px 11px;border-radius:11px;background:rgba(255,255,255,.027);color:#cbd5e1;font-size:.64rem;line-height:1.55}.reply{margin-top:9px;padding:11px;border-radius:12px;background:linear-gradient(135deg,rgba(99,102,241,.1),rgba(168,85,247,.07));border:1px solid rgba(99,102,241,.18)}.reply-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:6px}.reply-head strong{color:#c4b5fd;font-size:.59rem}.reply-head small{font-size:.48rem;color:#64748b}.reply-text{font-size:.62rem;line-height:1.55;color:#e2e8f0}.waiting{display:inline-flex;align-items:center;gap:6px;margin-top:9px;padding:6px 9px;border-radius:50px;background:rgba(251,191,36,.07);border:1px solid rgba(251,191,36,.14);color:#fcd34d;font-size:.52rem;font-weight:800}.empty{text-align:center;padding:48px 20px;color:#64748b}.empty i{font-size:2rem;color:#818cf8;margin-bottom:10px}.empty strong{display:block;color:#cbd5e1;font-size:.75rem;margin-bottom:4px}.empty span{font-size:.58rem}
@media(max-width:850px){.layout{grid-template-columns:1fr}.compose{position:relative;top:auto}.navbar{height:70px;padding:8px 16px}.brand-logo{width:47px;height:47px}.brand-logo img{width:45px;height:45px}.wrapper{width:min(100% - 20px,1120px);padding-top:24px}.messages{max-height:none}}
@media(max-width:600px){.brand-text{display:none}.nav-btn span{display:none}.nav-btn{width:40px;padding:0;justify-content:center}.hero{padding:22px 18px}.hero h1{font-size:1.5rem}.hero:after{display:none}.compose,.inbox{padding:18px 15px}.thread-head{align-items:flex-start}}

/* ===== LIVE UI ANIMATIONS ===== */
@keyframes pageReveal{
    from{opacity:0;transform:translateY(14px)}
    to{opacity:1;transform:translateY(0)}
}
@keyframes softPulse{
    0%,100%{box-shadow:0 0 0 rgba(99,102,241,0)}
    50%{box-shadow:0 0 28px rgba(99,102,241,.16)}
}
@keyframes floatIcon{
    0%,100%{transform:translateY(0) rotate(0)}
    50%{transform:translateY(-4px) rotate(3deg)}
}
@keyframes livePulse{
    0%{box-shadow:0 0 0 0 rgba(74,222,128,.45)}
    70%{box-shadow:0 0 0 7px rgba(74,222,128,0)}
    100%{box-shadow:0 0 0 0 rgba(74,222,128,0)}
}

.hero,.compose,.inbox{
    animation:pageReveal .65s cubic-bezier(.2,.7,.2,1) both;
}
.compose{animation-delay:.08s}
.inbox{animation-delay:.16s}

.hero{
    transition:transform .3s ease,border-color .3s ease,box-shadow .3s ease;
}
.hero:hover{
    transform:translateY(-3px);
    border-color:rgba(99,102,241,.25);
    box-shadow:0 28px 75px rgba(0,0,0,.36),0 0 34px rgba(99,102,241,.08);
}

.panel{
    transition:transform .3s ease,border-color .3s ease,box-shadow .3s ease;
}
.panel:hover{
    border-color:rgba(99,102,241,.22);
    box-shadow:0 25px 65px rgba(0,0,0,.3),0 0 32px rgba(99,102,241,.07);
}

.brand-logo{
    transition:transform .3s ease,filter .3s ease;
}
.brand:hover .brand-logo{
    transform:scale(1.07) rotate(-2deg);
    filter:drop-shadow(0 0 12px rgba(168,85,247,.35));
}

.nav-btn{
    transition:transform .22s ease,background .22s ease,border-color .22s ease,color .22s ease,box-shadow .22s ease;
}
.nav-btn:hover{
    transform:translateY(-2px);
    border-color:rgba(99,102,241,.25);
    box-shadow:0 7px 20px rgba(99,102,241,.10);
}

.input{
    transition:border-color .25s ease,background .25s ease,box-shadow .25s ease,transform .25s ease;
}
.input:focus{
    transform:translateY(-1px);
    box-shadow:0 0 0 3px rgba(99,102,241,.08),0 0 24px rgba(99,102,241,.08);
}

.send{
    position:relative;
    overflow:hidden;
    transition:transform .25s ease,box-shadow .25s ease,filter .25s ease;
}
.send:before{
    content:"";
    position:absolute;
    top:0;
    left:-120%;
    width:70%;
    height:100%;
    background:linear-gradient(90deg,transparent,rgba(255,255,255,.20),transparent);
    transform:skewX(-20deg);
    transition:left .55s ease;
}
.send:hover:before{left:150%}
.send:hover{
    transform:translateY(-3px) scale(1.01);
    filter:brightness(1.08);
    box-shadow:0 15px 36px rgba(99,102,241,.34),0 0 28px rgba(168,85,247,.13);
}

.inbox-icon{
    transition:transform .3s ease,box-shadow .3s ease;
    animation:softPulse 3.5s ease-in-out infinite;
}
.inbox-head:hover .inbox-icon{
    transform:rotate(-6deg) scale(1.08);
    box-shadow:0 0 25px rgba(99,102,241,.18);
}

.support-badge i{
    color:#4ade80;
    animation:livePulse 1.8s infinite;
    border-radius:50%;
}

.thread{
    position:relative;
    overflow:hidden;
    transition:transform .25s ease,border-color .25s ease,background .25s ease,box-shadow .25s ease;
}
.thread:before{
    content:"";
    position:absolute;
    left:0;
    top:12%;
    bottom:12%;
    width:2px;
    border-radius:5px;
    background:linear-gradient(#6366f1,#a855f7);
    opacity:0;
    transition:opacity .25s ease;
}
.thread:hover{
    transform:translateY(-3px) translateX(2px);
    border-color:rgba(99,102,241,.28);
    background:rgba(99,102,241,.045);
    box-shadow:0 12px 28px rgba(0,0,0,.18),0 0 22px rgba(99,102,241,.055);
}
.thread:hover:before{opacity:1}

.avatar{
    transition:transform .28s ease,box-shadow .28s ease;
}
.thread:hover .avatar{
    transform:scale(1.08) rotate(-4deg);
    box-shadow:0 8px 22px rgba(99,102,241,.28);
}

.bubble{
    transition:background .25s ease,border-color .25s ease;
    border:1px solid transparent;
}
.thread:hover .bubble{
    background:rgba(255,255,255,.038);
    border-color:rgba(255,255,255,.045);
}

.reply{
    transition:transform .25s ease,border-color .25s ease,box-shadow .25s ease;
}
.reply:hover{
    transform:translateX(3px);
    border-color:rgba(168,85,247,.28);
    box-shadow:0 0 25px rgba(168,85,247,.07);
}

.waiting{
    transition:transform .22s ease,background .22s ease;
}
.waiting:hover{
    transform:translateX(3px);
    background:rgba(251,191,36,.11);
}

.eyebrow i,.panel-label i{
    animation:floatIcon 3s ease-in-out infinite;
}

@media (prefers-reduced-motion:reduce){
    *,*:before,*:after{
        animation-duration:.01ms!important;
        animation-iteration-count:1!important;
        transition-duration:.01ms!important;
        scroll-behavior:auto!important;
    }
}

</style>
</head>
<body>
<div class="orb one"></div><div class="orb two"></div>

<nav class="navbar">
    <a href="dashboard.php" class="brand">
        <div class="brand-logo"><img src="../logo-badminton.png" alt="Badminton Kampung Panji"></div>
        <div class="brand-text"><strong>BADMINTON</strong><span>KAMPUNG PANJI</span></div>
    </a>
    <div class="nav-actions">
        <a href="feedback_report.php" class="nav-btn"><i class="fa-solid fa-star"></i><span>Feedback</span></a>
        <a href="my_booking.php" class="nav-btn"><i class="fa-solid fa-calendar-check"></i><span>My Booking</span></a>
        <a href="dashboard.php" class="nav-btn"><i class="fa-solid fa-house"></i><span>Dashboard</span></a>
        <a href="../auth/logout.php" class="nav-btn logout"><i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span></a>
    </div>
</nav>

<main class="wrapper">
    <section class="hero">
        <span class="eyebrow"><i class="fa-solid fa-headset"></i> Player Support</span>
        <h1>Message & Questions</h1>
        <p>Ada soalan tentang booking, payment atau court? Hantar mesej kepada admin dan semak balasan anda terus di sini.</p>
    </section>

    <div class="layout">
        <section class="panel compose">
            <span class="panel-label"><i class="fa-solid fa-paper-plane"></i> New Message</span>
            <h2>How can we help?</h2>
            <p class="compose-intro">Tulis pertanyaan anda dengan jelas supaya admin boleh membantu dengan lebih cepat.</p>

            <?php if($success): ?>
                <div class="alert success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if($error): ?>
                <div class="alert error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label><i class="fa-solid fa-user"></i> Name</label>
                    <input class="input" value="<?= htmlspecialchars($name) ?>" readonly>
                </div>

                <div class="field">
                    <label><i class="fa-solid fa-envelope"></i> Email</label>
                    <input type="email" class="input" name="email" value="<?= htmlspecialchars($email) ?>" required>
                </div>

                <div class="field">
                    <label><i class="fa-solid fa-message"></i> Your Message</label>
                    <textarea class="input" name="message" placeholder="Type your question here..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>

                <button class="send" type="submit">
                    <i class="fa-solid fa-paper-plane"></i>&nbsp; Send Message
                </button>
            </form>

            <div class="safe"><i class="fa-solid fa-shield-halved"></i> Your message is sent directly to the admin.</div>
        </section>

        <section class="panel inbox">
            <div class="inbox-head">
                <div class="inbox-title">
                    <div class="inbox-icon"><i class="fa-regular fa-comments"></i></div>
                    <div><h2>My Conversations</h2><small>Your questions & admin replies</small></div>
                </div>
                <span class="support-badge"><i class="fa-solid fa-circle" style="font-size:5px"></i> SUPPORT</span>
            </div>

            <div class="messages">
            <?php if($messages && $messages->num_rows): ?>
                <?php while($row=$messages->fetch_assoc()): ?>
                    <article class="thread">
                        <div class="thread-head">
                            <div class="who">
                                <div class="avatar"><?= strtoupper(substr($name,0,1)) ?></div>
                                <div><strong>You</strong><small>Player message</small></div>
                            </div>
                            <span class="date"><i class="fa-regular fa-clock"></i> <?= htmlspecialchars(date('d M Y, h:i A',strtotime($row['created_at']))) ?></span>
                        </div>

                        <div class="bubble"><?= nl2br(htmlspecialchars($row['message'])) ?></div>

                        <?php if(!empty($row['admin_reply'])): ?>
                            <div class="reply">
                                <div class="reply-head">
                                    <strong><i class="fa-solid fa-shield-halved"></i> Admin Reply</strong>
                                    <?php if(!empty($row['replied_at'])): ?>
                                        <small><?= htmlspecialchars(date('d M Y, h:i A',strtotime($row['replied_at']))) ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="reply-text"><?= nl2br(htmlspecialchars($row['admin_reply'])) ?></div>
                            </div>
                        <?php else: ?>
                            <span class="waiting"><i class="fa-regular fa-clock"></i> Waiting for admin reply</span>
                        <?php endif; ?>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty">
                    <i class="fa-regular fa-comments"></i>
                    <strong>No messages yet</strong>
                    <span>Send your first question using the form.</span>
                </div>
            <?php endif; ?>
            </div>
        </section>
    </div>
</main>
</body>
</html>