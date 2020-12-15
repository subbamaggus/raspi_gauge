<?php
$result = [ "date" => date("Y-m-d H:i:s"),
            "value" => rand(1,99)];

$myresult = json_encode($result);

error_log($myresult);
echo $myresult;
?>
