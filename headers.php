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

function _403_()
{
  ap_header_("403 Access Denied");
  die();
}

function _500_()
{
  ap_header_("500 Server Error");
  die();
}

function _400_()
{
  ap_header_("400 Bad Request");
  die();
}

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