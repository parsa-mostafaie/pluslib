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

function redirect($url, $back = false, $backURL = null, $gen = false, $status=301)
{
  $params = [];
  if ($back) {
    $backURL ??= $_SERVER['REQUEST_URI'];
    $params['back'] = $backURL;
  }
  $url = url($url, $params);
  if ($gen)
    return $url;
  http_response_code($status);
  header('Location: ' . $url);
  die();
}

function API_ORIGIN_header()
{
  $accepted_origins = [www_url(''), ...pluslib\Config::$allowedOrigins];

  if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $accepted_origins)) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
  } else {
    _403_();
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