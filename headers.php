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
function _404_()
{
  ap_header_('404 Not Found', true, 404);
  die();
}

function redirect($url)
{
  header('Location: ' . $url);
  die();
}

function API_header()
{
  header('Access-Control-Allow-Origin: ' . $_SERVER['SERVER_NAME']);
}