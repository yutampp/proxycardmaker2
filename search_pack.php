<?php
if( isset($_POST['search_string']) ){
  $search_string = $_POST['search_string'];
}
else{
  $search_string = "https://www.db.yugioh-card.com/yugiohdb/card_search.action?ope=1&sess=1&pid=1122000&rp=99999";
  exit();
};

$db_file = "database";
$database = file_get_contents($db_file);
$database = json_decode($database);

$search_string = urlencode( $search_string );

$filter = function($v){
  global $search_string;
  return preg_match( "/".$search_string."/", $v[3] );
};

$result = array_filter($database,$filter);
$result = json_encode($result);

echo $result;


?>
