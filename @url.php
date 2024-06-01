<?php include_once 'init.php';
function c_url($url, $regularIt = true)
{
  return $regularIt ? regular_url(c_url($url, false)) : HOME_URL() . $url;
}
function web_url($url)
{
  return str_replace('\\', '/', $url);
}

function regular_url($_purl)
{
  return str_replace('/', DIRECTORY_SEPARATOR, web_url($_purl));
}

function i_protocol()
{
  $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? "https://" : "http://";
  return $protocol;
}

function http_baseurl()
{
  $baseurl = i_protocol() . $_SERVER["HTTP_HOST"];
  return $baseurl;
}

function etc_url($url)
{
  return $_SERVER['DOCUMENT_ROOT'] . $url;
}

function www_url($url){
  return http_baseurl() . web_url($url);
}

function form_processor_url($path, $dir = '/libs/custom/@form', $base = '')
{
  $base = $base ? $base : $_SERVER['DOCUMENT_ROOT'];
  $full = $base . $dir . $path;
  return $full;
}