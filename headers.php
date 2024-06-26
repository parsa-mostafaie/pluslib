<?php
defined('ABSPATH') || exit;

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

function redirect($url, $back = false, $backURL = null, $gen = false)
{
  if ($back) {
    $backURL = $backURL ?? $_SERVER['REQUEST_URI'];
    $sep = parse_url($url, PHP_URL_QUERY) ? '&' : '?';
    $url = $url . $sep . 'back=' . urlencode($backURL);
  }
  if ($gen)
    return $url;
  header('Location: ' . $url);
  die();
}

function API_ORIGIN_header()
{
  $accepted_origins = [www_url('')];
  if (isset($_SERVER['HTTP_ORIGIN'])) {
    // same-origin requests won't set an origin. If the origin is set, it must be valid.
    if (in_array($_SERVER['HTTP_ORIGIN'], $accepted_origins)) {
      header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    } else {
      _403_();
    }
  }
}


function API_header()
{
  API_ORIGIN_header();
}

function pls_content_type($type = 'application/json', $charset = 'utf8')
{
  header("Content-type: $type; charset=$charset");
}