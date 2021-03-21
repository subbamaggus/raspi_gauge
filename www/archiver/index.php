<?php

$auth = 'f03ada5ae38129d70e0b3c9992df812c';

if ($_GET['auth'] != $auth) {
    header('HTTP/1.0 401 Unauthorized');
    echo 'not allowed';
    exit;    
}

require("config.php");

class MyArchiverAPI {
  protected static $mysqli;

  function set_db_connection ($dbhost, $dbuser, $dbpass, $dbname) {

    self::$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

    if (self::$mysqli->connect_errno) {
      die("could not connect: " . self::$mysqli->connect_error);
    }
  }
  
  function storeData($data) {
    $sql = "INSERT INTO sensor_data (name, timestamp, value_no) VALUES (?, ?, ?)";
           
    $timestamp = strtotime($data['data']['date']);
    $value = "" . $data['data']['value'];
    $sensor = "data";
    
    $statement = self::$mysqli->prepare($sql);
    $statement->bind_param('sii', $sensor, $timestamp, $value);
    $statement->execute();

    //$result = "#" . $timestamp . "#" . $data['data']['date']. "#" . $data['data']['value'];

    $result = $statement->get_result();

    return $result;
  }
}

$myAPI = new MyArchiverAPI();
$myAPI->set_db_connection($host, $user, $password, $dbname);

$data = json_decode(file_get_contents('php://input'), true);
//print_r($data);
//echo $data["data"];

$result = $myAPI->storeData($data);
echo $result;

?>