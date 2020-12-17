<?php

require("config.php");

$lvl = 0;

function mylog($log_lvl, $log_msg) {
  if($log_lvl <= $GLOBALS['lvl'])
    error_log($log_msg);
}

if (isset($_GET['loglvl']))
  $lvl = $_GET['loglvl'];

$data_raw = file_get_contents($DATA_FILE_RAW);
$data_raw = substr($data_raw, 0, -1); 
mylog(0, $data_raw);

$data_ppm = file_get_contents($DATA_FILE_PPM);
$data_ppm = substr($data_ppm, 0, -1); 
mylog(0, $data_ppm);

$result = [ "date" => date("Y-m-d H:i:s"),
            "raw" => $data_raw,
            "ppm" => $data_ppm,
            "linear" => ($data_raw + 1) / $MAX_ADC_VALUE
          ];

$myresult = json_encode($result);

mylog(6, $myresult);
echo $myresult;
?>