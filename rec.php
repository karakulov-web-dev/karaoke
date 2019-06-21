<?php
require_once('./ReqTools.php');

$content = trim(file_get_contents("php://input"));
$body = json_decode($content);
$reqTools = new ReqTools();

if (gettype($body->mac) == "string") {
    $macd = $body->mac;
} else {
    $error = 'mac typeError';
}

if ($error) {
    echo "{\"error\":\"{$error}\"}";
}

if (!$error) {
    $bodyReq = array(
        'key' => $macd,
    );
    $result = $reqTools->reqPostHttp('https://votingpay.com/recordStart', $bodyReq);
    echo json_encode($result);
}

?>