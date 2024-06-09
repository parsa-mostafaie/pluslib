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

function pls_http_response_code($code)
{
  http_response_code($code);
  die();
}