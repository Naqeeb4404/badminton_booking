<?php



session_start();





// =====================================================

// CHECK ADMIN LOGIN

// =====================================================



if (

    !isset($_SESSION['user']) ||

    ($_SESSION['user']['role'] ?? '') !== 'admin'

) {

    header("Location: ../auth/login.php");

    exit();

}





// =====================================================

// DATABASE CONNECTION

// =====================================================



$db_path = __DIR__ . '/../config/db.php';



if (file_exists($db_path)) {

    include $db_path;

} else {

    die("Ralat: Fail pangkalan data (db.php) tidak dijumpai.");

}





if (!isset($conn) || !$conn) {

    die("Ralat: Sambungan ke pangkalan data gagal.");

}





// =====================================================

// CURRENT USER

// =====================================================



$user = $_SESSION['user'];





// =====================================================

// MARK ALL MESSAGE AS READ

// =====================================================

//

// Bila admin buka page message.php,

// semua message yang belum dibaca akan ditanda read.

//

// is_read:

// 0 = belum dibaca

// 1 = sudah dibaca

//

// =====================================================



mysqli_query(

    $conn,

    "UPDATE messages

     SET is_read = 1

     WHERE is_read = 0"

);





// =====================================================

// SEARCH

// =====================================================



$searchTerm = isset($_GET['search'])

    ? trim($_GET['search'])

    : '';





// =====================================================

// GET MESSAGES

// =====================================================



if ($searchTerm !== '') {



    $safeSearch = mysqli_real_escape_string(

        $conn,

        $searchTerm

    );



    $query = "

        SELECT \*

        FROM messages

        WHERE

            name LIKE '%$safeSearch%'

            OR email LIKE '%$safeSearch%'

            OR message LIKE '%$safeSearch%'

        ORDER BY id DESC

    ";



} else {



    $query = "

        SELECT \*

        FROM messages

        ORDER BY id DESC

    ";

}





$result = mysqli_query($conn, $query);



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





    <!-- Bootstrap -->



    <link

        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"

        rel="stylesheet"

    >





    <!-- Font Awesome -->



    <link

        rel="stylesheet"

        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"

    >





    <!-- Google Font -->



    <link

        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"

        rel="stylesheet"

    >





    <!-- Shared Sidebar CSS -->



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





        \* {



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

                    rgba(6, 182, 212, 0.08),

                    transparent 35%

                ),

                #07111f;



            color: var(--text-main);



        }





        /\* ==================================================

           MAIN CONTENT

        ================================================== \*/



        .main-content {



            margin-left: 350px;



            min-height: 100vh;



        }





        /\* ==================================================

           TOPBAR

        ================================================== \*/



        .topbar {



            height: 80px;



            display: flex;



            align-items: center;



            justify-content: space-between;



            padding: 0 42px;



            background:

                rgba(7, 17, 31, 0.90);



            border-bottom:

                1px solid rgba(

                    148,

                    163,

                    184,

                    0.10

                );



            position: sticky;



            top: 0;



            z-index: 50;



            backdrop-filter:

                blur(12px);



        }





        /\* ==================================================

           SEARCH

        ================================================== \*/



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

                rgba(

                    59,

                    130,

                    246,

                    0.12

                );



        }





        .search-form > i {



            position: absolute;



            left: 20px;



            top: 50%;



            transform:

                translateY(-50%);



            color: #7f8eaa;



            font-size: 18px;



            pointer-events: none;



        }





        /\* ==================================================

           ADMIN PILL

        ================================================== \*/



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



            transform:

                translateY(-1px);



        }





        /\* ==================================================

           CONTENT

        ================================================== \*/



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



            letter-spacing:

                -1px;



        }





        /\* ==================================================

           CARD

        ================================================== \*/



        .message-card {



            background:

                rgba(

                    13,

                    26,

                    45,

                    0.94

                );



            border:

                1px solid #19345c;



            border-radius: 24px;



            padding: 30px;



            box-shadow:

                0

                18px

                50px

                rgba(

                    0,

                    0,

                    0,

                    0.16

                );



        }





        /\* ==================================================

           TABLE

        ================================================== \*/



        .message-table {



            width: 100%;



            border-collapse:

                collapse;



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



            text-transform:

                uppercase;



            letter-spacing:

                0.2px;



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

                rgba(

                    148,

                    163,

                    184,

                    0.15

                );



            color: #e7edf8;



            vertical-align:

                middle;



            font-size: 15px;



        }





        .message-table tbody tr {



            transition:

                background 0.15s ease;



        }





        .message-table tbody tr:hover {



            background:

                rgba(

                    59,

                    130,

                    246,

                    0.05

                );



        }





        .message-name {



            font-weight: 800;



            color: #ffffff;



        }





        .message-text {



            max-width: 430px;



            white-space:

                normal;



            word-break:

                break-word;



            line-height: 1.6;



        }





        .message-date {



            color: #c5d0e2;



            white-space:

                nowrap;



        }





        /\* ==================================================

           REPLY BUTTON

        ================================================== \*/



        .reply-btn {



            display:

                inline-flex;



            align-items:

                center;



            justify-content:

                center;



            gap: 7px;



            padding:

                9px

                18px;



            border-radius:

                13px;



            background:

                linear-gradient(

                    135deg,

                    #3b82f6,

                    #2563eb

                );



            color: #ffffff;



            text-decoration:

                none;



            font-size: 13px;



            font-weight: 800;



            box-shadow:

                0

                8px

                20px

                rgba(

                    37,

                    99,

                    235,

                    0.20

                );



            transition:

                transform 0.15s ease,

                filter 0.15s ease;



        }





        .reply-btn:hover {



            color: #ffffff;



            transform:

                translateY(-1px);



            filter:

                brightness(1.08);



        }





        /\* ==================================================

           EMPTY

        ================================================== \*/



        .empty-message {



            padding: 45px;



            text-align: center;



            color: #94a3b8;



        }





        .empty-message i {



            display: block;



            margin-bottom: 12px;



            font-size: 35px;



            color: #64748b;



        }





        /\* ==================================================

           RESPONSIVE

        ================================================== \*/



        @media (

            max-width: 1100px

        ) {



            .main-content {



                margin-left:

                    280px;



            }



            .search-form {



                width:

                    300px;



            }



        }





        @media (

            max-width: 768px

        ) {



            .main-content {



                margin-left:

                    70px;



            }





            .topbar {



                padding:

                    0

                    18px;



            }





            .search-form {



                display:

                    none;



            }





            .admin-name {



                display:

                    none;



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



                font-size:

                    25px;



            }





            .message-card {



                padding:

                    15px;



            }



        }



    </style>



</head>





<body class="admin-page">





<!-- =====================================================

     SIDEBAR

\===================================================== -->



<?php



include __DIR__ . '/sidebar.php';



?>





<!-- =====================================================

     MAIN

\===================================================== -->



<div class="main-content">





    <!-- =================================================

         TOPBAR

    ================================================== -->



    <header class="topbar">





        <!-- SEARCH -->



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







        <!-- ADMIN -->



        <div class="topbar-right">





            <div class="user-pill">





                <div

                    class="user-avatar"

                    style="<?php

                        echo $admin_photo_style;

                    ?>"

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







    <!-- =================================================

         CONTENT

    ================================================== -->



    <main class="content-body">





        <!-- PAGE TITLE -->



        <div class="page-title">



            <i

                class="fa-solid fa-comments"

            ></i>



            <h1>

                Senarai Mesej & Pertanyaan

            </h1>



        </div>







        <!-- =================================================

             MESSAGE CARD

        ================================================== -->



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







                                <!-- NAME -->



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





                                    <?php



                                    $reply_email =

                                        trim(

                                            (string)(

                                                $row['email']

                                                ?? ''

                                            )

                                        );



                                    ?>





                                    <?php

                                    if (

                                        $reply_email !== ''

                                    ):

                                    ?>





                                        <a

                                            href="mailto:<?php

                                                echo htmlspecialchars(

                                                    $reply_email,

                                                    ENT_QUOTES,

                                                    'UTF-8'

                                                );

                                            ?>?subject=<?php

                                                echo rawurlencode(

                                                    'Balasan Pertanyaan Badminton Kampung Panji'

                                                );

                                            ?>"

                                            class="reply-btn"

                                        >



                                            <i

                                                class="fa-solid fa-reply"

                                            ></i>



                                            Balas



                                        </a>





                                    <?php else: ?>





                                        <span

                                            class="text-secondary small"

                                        >



                                            Tiada email



                                        </span>





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






<div id="replyModal" class="reply-modal">
    <div class="reply-modal-box">
        <div class="reply-modal-header">
            <h3><i class="fa-solid fa-reply"></i> Balas Message</h3>
            <button type="button" class="close-reply" onclick="closeReply()">&times;</button>
        </div>
        <div class="reply-to" id="replyUser"></div>
        <form method="POST">
            <input type="hidden" name="message_id" id="replyMessageId">
            <textarea name="admin_reply" id="adminReply" placeholder="Tulis balasan kepada user..." required></textarea>
            <button type="submit" name="reply_message" class="send-reply-btn"><i class="fa-solid fa-paper-plane"></i> Hantar Balasan</button>
        </form>
    </div>
</div>
<script>
function openReply(id,name,currentReply){
    document.getElementById('replyMessageId').value=id;
    document.getElementById('replyUser').textContent='Balas kepada: '+name;
    document.getElementById('adminReply').value=currentReply || '';
    document.getElementById('replyModal').style.display='flex';
}
function closeReply(){document.getElementById('replyModal').style.display='none';}
document.getElementById('replyModal').addEventListener('click',function(e){if(e.target===this)closeReply();});
</script>
</body>



</html                                <!-- ACTION -->
                                <td class="text-end">
                                    <div class="action-buttons">
                                        <button type="button" class="reply-btn"
                                            onclick='openReply(<?php echo (int)$row["id"]; ?>, <?php echo json_encode($row["name"] ?? "User"); ?>, <?php echo json_encode($row["admin_reply"] ?? ""); ?>)'>
                                            <i class="fa-solid fa-reply"></i> Balas
                                        </button>

                                        <form method="POST" style="display:inline" onsubmit="return confirm('Delete message ini?');">
                                            <input type="hidden" name="message_id" value="<?php echo (int)$row['id']; ?>">
                                            <button type="submit" name="delete_message" class="delete-btn">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>

                                    <?php if (!empty($row['admin_reply'])): ?>
                                        <div class="reply-preview">
                                            <strong>Admin reply:</strong><br>
                                            <?php echo nl2br(htmlspecialchars($row['admin_reply'], ENT_QUOTES, 'UTF-8')); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

>