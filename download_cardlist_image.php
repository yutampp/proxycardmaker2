<?php

ini_set('display_errors',1);
require_once("./phpQuery-onefile.php");
/*
$hoge = download_cardlist_image( "https://www.db.yugioh-card.com/yugiohdb/card_search.action?ope=1&sess=1&pid=3308021&rp=99999" );
$hoge = array_map(function($v){
  return "<img src='data:image/png;base64,".$v."'>";
  },$hoge);
echo implode("\n",$hoge);

$card_url = "https://www.db.yugioh-card.com/yugiohdb/card_list.action";
echo "<textarea wrap=off style='width:100%; height:100%'>";
echo download_cardlist( $card_url );
echo "</textarea>";
*/

$card_url = "https://www.db.yugioh-card.com/yugiohdb/card_list.action";
$packs = download_cardlist( $card_url );
$dst_save = "./packs/summary";
file_put_contents( $dst_save, json_encode($packs) );
foreach($packs as $key => $value){
  echo $value . " start\n";
  download_cardlist_image( $key );
  echo $value . " end\n";
}

$file_name = "card_name_cid_bind.php";
require($file_name);

$file_name = "card_detail_cid_bind.php";
exec("/usr/bin/php ".$file_name);

/*
$packs = file_get_contents( "./cardlist");
foreach(explode("\n",$packs) as $value){
  $value = urldecode($value);
  $url = str_replace("texts/","",$value);
  download_cardlist_image( $url );
  echo $url;
}
*/
function download_cardlist( $card_url ){
  $domain = "https://www.db.yugioh-card.com";
  // Referer ga naito error ga hasssei.
  // Language ga naito eigo ni naru.
  // souiu riyuu de http header rewrite ga hissu.
  $opts = array(
    'http' => array(
      'method' => "GET",
      'header' => "Referer: ".$card_url ."\r\n" .
                  "Accept-Language: ja,en-US;q=0.9,en;q=0.8\r\n"
    )
  );
  $context = stream_context_create($opts);

  $html = file_get_contents($card_url, false, $context);
  $doc = phpQuery::newDocument($html);
  
  $packs = array();
  foreach( $doc["div.pack_ja"] as $pack ){
    $pack_name = pq($pack)->text();
    $pack_name = str_replace(["\r\n","\t"],"",$pack_name);
    $pack_url = pq($pack)->find("input")->attr("value");
    $pack_url = $domain . $pack_url;
    $packs[$pack_url] = $pack_name;
    $dst_save = "./packs/" . urlencode($pack_url);
    file_put_contents( $dst_save, $pack_name );
  }

  return $packs;
}

function download_cardlist_image( $card_url ){

  $domain = "https://www.db.yugioh-card.com";
  // Referer ga naito error ga hasssei.
  // Language ga naito eigo ni naru.
  // souiu riyuu de http header rewrite ga hissu.
  $opts = array(
    'http' => array(
      'method' => "GET",
      'header' => "Referer: ".$card_url ."\r\n" .
                  "Accept-Language: ja,en-US;q=0.9,en;q=0.8\r\n"
    )
  );
  $context = stream_context_create($opts);
  $file_name = "./texts/" . urlencode($card_url);
  if( file_exists($file_name) and filesize($file_name)!==0 ){
    $html = file_get_contents( $file_name );
  }
  else{
    $html = file_get_contents($card_url, false, $context);
    $dst_save = "./texts/" . urlencode( $card_url );
    file_put_contents( $dst_save, $html );
  }

  $rex = "/\\\$\('#card_image_[0-9]{1,3}_[0-9]{1,3}'\)\.attr\('src', '[^']*'/";
  $magic_strings = preg_grep( $rex, explode("\n",$html) );


  $card_images = array();
  foreach( $magic_strings as $value ){
    $card_img_url = $domain . explode("'",$value )[5];
    $cid = preg_replace("/.*cid=([0-9]+).*/","\\1",$card_img_url);
    $file_name = "images/" . $cid . ".png";
    echo $file_name . "\n";
    if( file_exists($file_name) ){
      echo $file_name . "\n";
      $card_img = file_get_contents( $file_name );
    }
    else{
      echo $card_img_url . "\n";
      $card_img = download_card_image( $card_img_url );
    }
    array_push($card_images, $card_img );
  }

  return $card_images;


}

function download_card_image( $card_url ){

  $domain = "https://www.db.yugioh-card.com";
  // Referer ga naito error ga hasssei.
  // Language ga naito eigo ni naru.
  // souiu riyuu de http header rewrite ga hissu.
  $opts = array(
    'http' => array(
      'method' => "GET",
      'header' => "Referer: ".$card_url ."\r\n" .
                  "Accept-Language: ja,en-US;q=0.9,en;q=0.8\r\n"
    )
  );
  $context = stream_context_create($opts);

  $card_img = file_get_contents($card_url, false, $context);

  $img = imagecreatefromstring( $card_img );

  $cid = preg_replace("/.*cid=([0-9]+)&.*/","\\1",$card_url);
  $dst_save = "./images/" . $cid . ".png";
  file_put_contents( $dst_save, $card_img );

  $card_img_b64 = base64_encode($card_img);
  //  $card_img_b64 = Base64encodeFromResouceID($new_img);

  return $card_img_b64;
}

?>
