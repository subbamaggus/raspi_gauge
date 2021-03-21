<?php

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

?>