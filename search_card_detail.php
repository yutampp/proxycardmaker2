<?php
ini_set('display_errors',1);
if( isset($_POST['cids']) ){
  $cids = $_POST['cids'];
  $cids = json_decode($cids);
  $rex =  "/" . implode("|",$cids) . "/";
}
else{
  $cids = [7890,10200];
  $rex = "/" . implode("|",$cids) . "/";
  exit();
};

$db_file = "database_detail";
$database = file_get_contents($db_file);
$database = json_decode($database);

$filter = function($v){
  global $rex;
  return preg_match($rex,$v[0]);
};

$result = array_filter($database,$filter);
$result = json_encode($result);

echo $result;


?>
