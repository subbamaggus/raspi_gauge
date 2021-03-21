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
    
    $date = new DateTime($data['data']['date'], new DateTimeZone('UTC'));
    $timestamp = $date->format('U');

    $value = "" . $data['data']['value'];
    $sensor = "data";
    
    $statement = self::$mysqli->prepare($sql);
    $statement->bind_param('sii', $sensor, $timestamp, $value);
    $statement->execute();

    //$result = "#" . $timestamp . "#" . $data['data']['date']. "#" . $data['data']['value'];

    $result = $statement->get_result();

    return $result;
  }
  
  function getLatestRecord($sensor) {
    $sql = "SELECT * FROM sensor_data" .
           " WHERE name = ?  ORDER BY id DESC LIMIT 1";
           
    $statement = self::$mysqli->prepare($sql);
    $statement->bind_param('s', $sensor);
    $statement->execute();
    
    $result = $statement->get_result();

    $all_items = mysqli_fetch_all($result,MYSQLI_ASSOC);
    
    $ts = $all_items[0]['timestamp'];
    $date = date('Y-m-d H:i:s e', $ts);
    
    $value = $all_items[0]['value_no'];
    
    $result = [ "date" => $date,
            "raw" => $value,
            "ppm" => $value,
            "ugpm3" => $value,
            "linear" => $value
          ];
          
    return $result;
  }
}

?>