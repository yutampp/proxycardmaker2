<?php
  $cid = $_GET["cid"];
  $file_name = "images/" . $cid . ".png";
  $png = file_get_contents($file_name);
  $png_b64 = base64_encode($png);
  $result = array("cid" => $cid,
             "png_b64" => $png_b64);
  echo json_encode($result);
?>
