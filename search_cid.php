<?php
if( isset($_POST['search_string']) ){
  $search_string = $_POST['search_string'];
}
else{
  //$search_string = "100";
  exit();
};

$db_file = "database";
$database = file_get_contents($db_file);
$database = json_decode($database);

$filter = function($v){
  global $search_string;
  return preg_match( "/".$search_string."/", $v[2] );
};

$result = array_filter($database,$filter);
$result = json_encode($result);

echo $result;


?>
