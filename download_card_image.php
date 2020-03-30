<?php

ini_set('display_errors',1);
require_once("./phpQuery-onefile.php");

define('TEST_URL', "https://www.db.yugioh-card.com/yugiohdb/card_search.action?ope=2&cid=13463&request_locale=ja" );
define('DOMAIN', "https://www.db.yugioh-card.com" );

echo "<img src='data:image/png;base64,";
echo download_card_image( $_POST['url']);
echo "'>";

function download_card_image( $card_url ){
  $domain = DOMAIN;
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


  // card no setumei page wo torini iku.
  $html = file_get_contents($card_url, false, $context);
  // betu ni iranai.
  $doc = phpQuery::newDocument($html);

  // cid wo nukidasu. betu ni iranai.
  $cid = preg_replace("/.*cid=(.+)/","\\1",$card_url);

  // card image no url ga kaitearu gyou ( = magic_string ) wo nukidasu. sitamitai na yatu.
  // magic_string -> $('#card_image_1').attr('src', '/yugiohdb/get_image.action?type=2&cid=14809&ciid=1&enc=VBbN3HTZyAisMta77vQEVw').show();
  $magic_string =  implode( preg_grep(  "/.*'#card_image_1'.*/", explode("\n",$doc) ) );
  // card image no url dake nukitoru.
  $card_img_url = $domain . explode("'",$magic_string )[5];

  // card image wo torini iku.
  $card_img = file_get_contents($card_img_url, false, $context);

  $img = imagecreatefromstring( $card_img );

  $new_img = imagecreatetruecolor(imagesx($img)*2, imagesy($img)*2 );
  imagecopyresized($new_img, $img, 0, 0, 0, 0, imagesx($img)*2, imagesy($img)*2,
                                               imagesx($img), imagesy($img) );

  $card_img_b64 = base64_encode($card_img);
  //  $card_img_b64 = Base64encodeFromResouceID($new_img);

  return $card_img_b64;
}

function Base64encodeFromResouceID($gdid){
  ob_start();
  imagepng($gdid, null, 6);
  $raw_string = ob_get_clean();
  return base64_encode( $raw_string );
}
?>
