<?php

require("config.php");

$lvl = 0;

function mylog($log_lvl, $log_msg) {
  if($log_lvl <= $GLOBALS['lvl'])
    error_log($log_msg);
}

function getDataFromFile($filename) {
  $data = file_get_contents($filename);
  $data = substr($data, 0, -1); 
  mylog(0, $data);

  return $data;    
}

if (isset($_GET['loglvl']))
  $lvl = $_GET['loglvl'];

$data_raw = getDataFromFile($DATA_FILE_RAW);
$data_ppm = getDataFromFile($DATA_FILE_PPM);
$data_ugpm3 = getDataFromFile($DATA_FILE_UGPM3);

$result = [ "date" => date("Y-m-d H:i:s"),
            "raw" => $data_raw,
            "ppm" => $data_ppm,
            "ugpm3" => $data_ugpm3,
            "linear" => ($data_raw + 1) / $MAX_ADC_VALUE
          ];

$myresult = json_encode($result);

mylog(6, $myresult);
echo $myresult;
?>