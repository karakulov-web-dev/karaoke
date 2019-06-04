<?php
require_once('./ReqTools.php');

$content = trim(file_get_contents("php://input"));
$body = json_decode($content);

if (gettype($body->karaoke_item_id) == "integer") {
    $serialId = $body->serialId;
} else {
    $error = 'karaoke_item_id typeError (should: karaoke_item_id == integer)';
}


if ($error) {
    echo "{\"error\":\"{$error}\"}";
}

if (!$error) {
    $reqTools = new ReqTools();
    $sql = "SELECT COUNT(*) FROM `favorites` WHERE userMac='$userMac'";
    //$result = $reqTools->reqDb($sql);
    echo "{\"error\"}:\"false\"";
}

?>