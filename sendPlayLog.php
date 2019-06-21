<?php
require_once('./ReqTools.php');
require_once('./PlayLogParser.php');
require_once('./FFmpegAmix.php');

$content = trim(file_get_contents("php://input"));
$body = json_decode($content);
$reqTools = new ReqTools();

if (gettype($body->mac) == "string") {
    $macd = $body->mac;
} else {
    $error = 'mac typeError';
}

if (gettype($body->log) == "array") {
    $log = $body->log;
} else {
    $error = 'log typeError';
}

if ($error) {
    exit ("{\"error\":\"{$error}\"}");
}

//останавливаем запись если она еще не остановленна
$bodyReq = array(
   'key' => $macd,
);
$reqTools->reqPostHttp('https://votingpay.com/recordStop', $bodyReq);

// получаем и сортируем записи
function cmpRecords ($a,$b) {
    return  $a->time->startTime - $b->time->startTime;
}
$records =  $reqTools->reqPostHttp('https://votingpay.com/getRecords', $bodyReq);
$records = $records->files;
usort($records, 'cmpRecords');

// Делим записи по непрерывным интервалам / вычисляем задержку для каждой записи 
$log = new PlayLogParser($log);
foreach ($log->intervals as $interval) {
    $interval->setRecords($records);
}

$records = array();
foreach ($log->intervals as $interval) {
    $records[] = $interval->records;
}

$originalVideoRecord = new stdClass();
$originalVideoRecord->file = new stdClass();
$originalVideoRecord->file->url = "http://212.77.128.233/media/tv-strg-09/$macd/$log->contentId.mpg";

$records = call_user_func_array('array_merge',$records);
array_unshift($records, $originalVideoRecord);


//$contentArr = $reqTools->reqDb("SELECT * FROM `stalker_db`.`karaoke` WHERE  `id`=$log->contentId");

//$content= $contentArr[0];


//$sql = "INSERT INTO `stalker_db`.`karaoke`".
//" (`name`, `description`, `protocol`, `author`, `accessed`, `status`, `added`, `add_by`, `done`, `done_time`, `countView`, `karaokePreview`, `clientMac`)".
//" VALUES ('".$content['name']."', '".$content['description']."', '".$content['protocol']."', '".$content['author']."', '".$content['accessed']."', '".$content['status']."', '".$content['added']."',".
//" '".$content['add_by']."', '".$content['done']."', '".$content['done_time']."', '".$content['countView']."', '".$content['karaokePreview']."', '$macd') ";

//$reqTools->reqDb($sql);

//$LAST_INSERT_ID = $reqTools->reqDb('SELECT LAST_INSERT_ID()')[0]['LAST_INSERT_ID()'];

//$LAST_INSERT_ID = 1201;
$body = array(
    'records' => $records,
    'contentId' => $log->contentId,
    'mac'  => $macd
);
$body = escapeshellarg(serialize($body));

$stderr_ouput = array();
exec("php /var/www/karakulov/karaoke/karaokeFFmpeg.php $body > /var/www/karakulov/karaoke/log.txt &");




