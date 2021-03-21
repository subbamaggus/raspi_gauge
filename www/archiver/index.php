<?php

$auth = 'f03ada5ae38129d70e0b3c9992df812c';

if ($_GET['auth'] != $auth) {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'not allowed';
    exit;    
}

$data = json_decode(file_get_contents('php://input'), true);
print_r($data);
echo $data["data"];

?>