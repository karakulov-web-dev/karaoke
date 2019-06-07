<?php
require_once('bdConfig.php');
$mysqli = new mysqli($bdConfig->host, $bdConfig->username, $bdConfig->password, $bdConfig->bdName, $bdConfig->port);
$mysqli->set_charset("utf8");
class ReqTools {
    function reqDb ($sql) {
        global $mysqli;
        $res = $mysqli->query($sql);
        $result = array();
        if (gettype($res) == 'object') {
            while ($row = $res->fetch_assoc()) {
                $result[] = $row;
            }
        }
        return $result;
    }
    function reqGetHttp($url) {
        $opts = array(
            'http' =>
                array(
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 
                    'method'  => 'GET'
                )
        );
        $context  = stream_context_create($opts);
        $data = json_decode(file_get_contents($url, false, $context));
        return $data;
    }
}
?>