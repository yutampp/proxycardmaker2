<?php
ini_set('display_errors',1);
require_once("./phpQuery-onefile.php");
/*
var_dump( loadtext("texts/https%3A%2F%2Fwww.db.yugioh-card.com%2Fyugiohdb%2Fcard_search.action%3Fope%3D1%26sess%3D1%26pid%3D4401017%26rp%3D99999") );
*/

$pack_list = get_pack_list();
/*
var_dump( $pack_list);
*/
$database = array();
foreach( $pack_list as $key => $value){
  $path = "texts/" . urlencode( $key );
  $exists = file_exists( $path );
  if( $exists ){
    $result = loadtext($path);
    $database = array_merge( $database, $result );
    var_dump( count($database) );
  }
  else{
    echo $path . "\n" ;
  }
}

file_put_contents( "database", json_encode($database) );


function loadtext( $path ){
  $html = file_get_contents( $path );
  $doc = phpQuery::newDocument( $html );
  $card_attributes = array();
  $search_strings = array();
  $card_names = array();
  $cids = array();
  $summary = array();
  $lists = $doc[".box_list > li"];
  foreach($lists as $li ){
    $card_attribute = pq($li)->find("span.box_card_attribute > span")->text();
    $catd_attribute = str_replace(["\r\n","\t"],"", $card_attribute);
    $search_string = pq($li)->find(".box_card_name")->text();
    $search_string = str_replace(["\r\n","\t"],"",$search_string);
    array_push( $search_strings, $search_string );
    $card_name = pq($li)->find(".box_card_name > .card_status")->text();
    $card_name = str_replace(["\r\n","\t"],"",$card_name);
    array_push( $card_names, $card_name );
    $cid = pq($li)->find( "input" )->val();
    $cid = preg_replace( "/.*cid=([0-9]+)\$/", "\\1",$cid );
    array_push( $cids, $cid );
    array_push( $summary, [$cid, $card_name, $search_string, $path, $card_attribute ] );
  }
  return $summary;
}

function get_pack_list(){
  $pack_list_string = file_get_contents( "./packs/summary");
  $pack_list = json_decode( $pack_list_string );
  return $pack_list;
}
?>
