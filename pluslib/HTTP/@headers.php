<?php

use pluslib\HTTP\HTTPException;

function redirect($url, $back = false, $backURL = null, $status = 301)
{
  $params = [];

  if ($back) {
    $backURL ??= $_SERVER['REQUEST_URI'];
    $params['back'] = $backURL;
  }

  $url = url($url, $params);

  return response()->redirect($url, $status);
}

function API_ORIGIN_header()
{
  $accepted_origins = [url(''), ...app()->friend_origins];

  if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $accepted_origins)) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
  } else {
    abort(403);
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

function abort($code, $message = '', $headers = [])
{
  throw new HTTPException($code, $message, $headers);
}