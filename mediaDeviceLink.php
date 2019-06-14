<?php
require_once('./ReqTools.php');

$content = trim(file_get_contents("php://input"));
$body = json_decode($content);

if (gettype($body->mac) == "string") {
    $macd = $body->mac;
} else {
    $error = 'mac typeError';
}

if ($error) {
    echo "{\"error\":\"{$error}\"}";
}

if (!$error) {
    echo "{\"error\":\"false\",\"link\":\"http://device-test.ru/RTF3\"}";
}

?>