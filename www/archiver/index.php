<?php

$auth = 'f03ada5ae38129d70e0b3c9992df812c';

if ($_GET['auth'] != $auth) {
    header('HTTP/1.0 401 Unauthorized');
    echo 'not allowed';
    exit;    
}

require("config.php");
require("archiver.php");

$myAPI = new MyArchiverAPI();
$myAPI->set_db_connection($host, $user, $password, $dbname);

$data = json_decode(file_get_contents('php://input'), true);

$result = $myAPI->storeData($data);

echo $result;

?>