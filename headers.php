<?php
require_once 'init.php';
function ap_header_(
  string $header,
  bool $replace = true,
  int $response_code = 0
) {
  header(
    $_SERVER['SERVER_PROTOCOL'] . ' ' . $header,
    $replace,
    $response_code
  );
  die();
}

include_once 'http_status.php';

function redirect($url)
{
  header('Location: ' . $url);
  die();
}

function API_ORIGIN_header()
{
  header('Access-Control-Allow-Origin: ' . $_SERVER['SERVER_NAME']);
}


function API_header()
{
  API_ORIGIN_header();
}