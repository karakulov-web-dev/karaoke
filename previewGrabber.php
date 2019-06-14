<?php
require_once('./ReqTools.php');

    $reqTools = new ReqTools();
    $sql = "SELECT name, singer, id FROM `stalker_db`.`karaoke`";
    $result = $reqTools->reqDb($sql);

foreach ($result as $item) {

    $data = $reqTools->reqGetHttp(
        "https://www.googleapis.com/youtube/v3/search?".
        "part=snippet&relevanceLanguage=RU&order=relevance&".
        "type=video&maxResults=1&regionCode=RU&key=AIzaSyDwfhWw-XUDSh_HQ38965RU_VBRqzVC6i8&".
        "q=".urlencode($item['name']." ".$item['singer']));

     try {
      $url = $data->items[0]->snippet->thumbnails->medium->url;
      $fileName = md5($url).".jpg";
      file_put_contents("/var/www/stalker_portal/misc/karaokePreview/$fileName", file_get_contents($url));
      $sql = "UPDATE `stalker_db`.`karaoke` SET karaokePreview = 'http://212.77.128.177/stalker_portal/misc/karaokePreview/$fileName' WHERE id = ".$item['id'];
      $reqTools->reqDb($sql);
     } catch(Exception $e) {}
} 

?>