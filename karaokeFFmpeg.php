<?php
require_once('./FFmpegAmix.php');
require_once('./ReqTools.php');
//require_once('./passthru_with_errors.php');

$conf = unserialize($argv[1]);
$conf['fileName'] = rand().".mpg";
$contentId = $conf['contentId'];
$mac = $conf['mac'];

$ffmpegAmix = new FFmpegAmix('/var/www/karakulov/karaoke/ffmpeg', $conf['fileName'], $conf['records']);
$ffmpegAmix->exec();
echo $ffmpegAmix->ffmpegString;

$reqTools = new ReqTools();

$contentArr = $reqTools->reqDb("SELECT * FROM `stalker_db`.`karaoke` WHERE  `id`=$contentId");

$content = $contentArr[0];
$sql = "INSERT INTO `stalker_db`.`karaoke`".
" (`name`, `description`, `protocol`, `author`, `accessed`, `status`, `added`, `add_by`, `done`, `done_time`, `countView`, `karaokePreview`, `clientMac`)".
" VALUES ('".$content['name']."', '".$content['description']."', '".$content['protocol']."', '".$content['author']."', '".$content['accessed']."', '".$content['status']."', '".$content['added']."',".
" '".$content['add_by']."', '".$content['done']."', '".$content['done_time']."', '".$content['countView']."', '".$content['karaokePreview']."', '$mac') ";
$reqTools->reqDb($sql);
$LAST_INSERT_ID = $reqTools->reqDb('SELECT LAST_INSERT_ID()')[0]['LAST_INSERT_ID()'];

$data = array(
    'fileName' => $LAST_INSERT_ID.".mpg",
    'url' => "http://212.77.128.177/karakulov/karaoke/".$conf['fileName']
);
$result = $reqTools->reqPostHttpJson('http://212.77.128.233/stalker_portal/storage/karaoke/download.php', $data);

unlink($conf['fileName']);

?>