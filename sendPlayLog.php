<?php
require_once('./ReqTools.php');

$content = trim(file_get_contents("php://input"));
$body = json_decode($content);

if (gettype($body->log) == "array") {
    $log = $body->log;
} else {
    $error = 'log typeError';
}

if ($error) {
    echo "{\"error\":\"{$error}\"}";
}

if (!$error) {
    echo "{\"error\":\"false\"}";
}

?>