<?php

// Use the first autoload instead if you don't want to install composer
//require_once '../autoload.php';
require_once '../autoload.php';

$url ="https://www.acisonline.net/?lang=th";



$screen = new Screen\Capture($url);
$screen->setWidth(intval('1024'));
$screen->setHeight('768');
$screen->setClipWidth(intval("0"));
$screen->setClipHeight(intval("0"));
$screen->setUserAgentString("e.g.: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.87 Safari/537.36");
$screen->setBackgroundColor("#ffffff");
$screen->setImageType("png");

$a1 = mt_rand(100000,999999); 
$a2 = mt_rand(100000,999999); 
$url1 = $url;
$parse = parse_url($url);
$fileLocation = $a1.$a2;
$screen->save($fileLocation);

header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
header('Content-Type:' . $screen->getImageType()->getMimeType());
header('Content-Length: ' . filesize($screen->getImageLocation()));
readfile($screen->getImageLocation());
