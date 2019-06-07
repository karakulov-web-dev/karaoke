<?php
require_once('./ReqTools.php');

$content = trim(file_get_contents("php://input"));
$body = json_decode($content);

if (gettype($body->karaoke_item_id) == "integer") {
    $karaoke_item_id = $body->karaoke_item_id;
} else {
    $error = 'karaoke_item_id typeError (should: karaoke_item_id == integer)';
}


if ($error) {
    echo "{\"error\":\"{$error}\"}";
}

if (!$error) {
    $reqTools = new ReqTools();
    $sql = "UPDATE `stalker_db`.`karaoke` SET countView = countView - 1 WHERE id = {$karaoke_item_id}";
    $result = $reqTools->reqDb($sql);
    echo "{\"error\":\"false\"}";
}

?>