<?php
defined('ABSPATH') || exit;

function _404_()
{
  ap_header_('404 Not Found', true, 404);
  die();
}

function _403_()
{
  ap_header_("403 Access Denied", true, 403);
  die();
}

function _500_()
{
  ap_header_("500 Server Error", true, 500);
  die();
}

function _400_()
{
  ap_header_("400 Bad Request", true, 400);
  die();
}

function pls_http_response_code($code, $text = null, $live = false)
{
  if (!$text) {
    http_response_code($code);
  } else {
    ap_header_("$code $text", true, $code);
  }
  !$live && die();
}

function pls_invalid_http_method($live = false, $message = null)
{
  pls_http_response_code(405, $message, $live);
}

function pls_validate_http_method($method = 'post', $live = false, $message = null)
{
  if (!request_method($method))
    pls_invalid_http_method();
}