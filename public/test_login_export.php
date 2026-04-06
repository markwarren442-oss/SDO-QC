<?php
$cookie_jar = tempnam(sys_get_temp_dir(), 'cookies');
$ch = curl_init('http://localhost/SDO-QC/SDO-QC-Laravel/public/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
$html = curl_exec($ch);
curl_close($ch);
preg_match('/name="_token" value="(.*?)"/', $html, $matches);
if (empty($matches)) die('no token');
$token = $matches[1];

$ch = curl_init('http://localhost/SDO-QC/SDO-QC-Laravel/public/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['_token' => $token, 'username' => 'admin', 'password' => 'admin123']));
curl_exec($ch);
curl_close($ch);

$ch = curl_init('http://localhost/SDO-QC/SDO-QC-Laravel/public/admin/export/attendance?year=2026&month=3&station=All');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
$export = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($code !== 200) { echo "HTTP $code\n"; echo substr($export, 0, 1000); } else { file_put_contents('out.xlsx', $export); echo "SUCCESS, size: " . strlen($export); }
