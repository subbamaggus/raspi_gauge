<?php

$lvl = 0;

function mylog($log_lvl, $log_msg) {
  if($log_lvl <= $GLOBALS['lvl'])
	error_log($log_msg);
}

if (isset($_GET['loglvl']))
  $lvl = $_GET['loglvl'];

$data = file_get_contents("spi_data");
$data = substr($data, 0, -1); 
mylog(0, $data);

$result = [ "date" => date("Y-m-d H:i:s"),
            "value" => $data];

$myresult = json_encode($result);

mylog(6, $myresult);
echo $myresult;
?>