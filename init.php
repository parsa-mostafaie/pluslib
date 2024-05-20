<?php
function HOME_URL($set = null)
{
  static $hu = '';
  if ($set) {
    $hu = $set;
  }
  return $hu;
}
//? libs:init.php v0.5.1
//! Publics
include_once 'array.php';
include_once '@form/processors/@processor.php';
include_once '@form/get_post.php';
include_once '@security/upload.php';
include_once '@security/security.php';
include_once 'session.php';
include_once 'sql.php';
include_once 'oop.sql.php';
include_once 'queries.oop.sql.php';
include_once '@user/auth.php';
include_once "info.php";
include_once '@form/input_validation.php';
include_once 'headers.php';
include_once '@ajax.php';

//! For This Project
include_once '@user/user.php';
include_once '@user/follow.php';

//! ADMIN
include_once "admin/lib.php";

function c_url($url, $regularIt = true)
{
  return $regularIt ? regular_url(c_url($url, false)) : HOME_URL() . $url;
}
function web_url($url)
{
  return str_replace('\\', '/', $url);
}
function number_format_short($n, $precision = 1)
{
  if ($n < 900) {
    // 0 - 900
    $n_format = number_format($n, $precision);
    $suffix = '';
  } else if ($n < 900000) {
    // 0.9k-850k
    $n_format = number_format($n / 1000, $precision);
    $suffix = 'K';
  } else if ($n < 900000000) {
    // 0.9m-850m
    $n_format = number_format($n / 1000000, $precision);
    $suffix = 'M';
  } else if ($n < 900000000000) {
    // 0.9b-850b
    $n_format = number_format($n / 1000000000, $precision);
    $suffix = 'B';
  } else {
    // 0.9t+
    $n_format = number_format($n / 1000000000000, $precision);
    $suffix = 'T';
  }

  // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
  // Intentionally does not affect partials, eg "1.50" -> "1.50"
  if ($precision > 0) {
    $dotzero = '.' . str_repeat('0', $precision);
    $n_format = str_replace($dotzero, '', $n_format);
  }

  return $n_format . $suffix;
}


function regular_url($_purl)
{
  return str_replace('/', DIRECTORY_SEPARATOR, $_purl);
}

function imageComponent($purl, $cattr = '', $undefined = '/default_uploads/unknown.png', $echo = false, $ue_src = true)
{
  $ud = $ue_src ? 'this.src' : 'this.style.background';
  $ud = "$ud = '$undefined';";
  $str = '<img loading="lazy" src="' . $purl . '" onerror="this.onerror=null;' . $ud . '" ' . $cattr . '>
';
  if ($echo)
    echo $str;
  return $str;
}

function divImage($purl, $cattr, $undefined_color = 'ffaabb', $echo = false)
{
  $purl = web_url($purl);
  $str = "<div style='background: url($purl), #$undefined_color; background-size: cover' $cattr></div>";
  if ($echo)
    echo $str;
  return $str;
}

function form_processor_url($path, $dir = '/libs/custom/@form', $base = '')
{
  $base = $base ? $base : $_SERVER['DOCUMENT_ROOT'];
  $full = $base . $dir . $path;
  return $full;
}

function truncate($string, $chars = 50, $terminator = ' …')
{
  $cutPos = $chars - mb_strlen($terminator);
  $boundaryPos = mb_strrpos(mb_substr($string, 0, mb_strpos($string, ' ', $cutPos)), ' ');
  return mb_substr($string, 0, $boundaryPos === false ? $cutPos : $boundaryPos) . $terminator;
}

function valueof($fv, $data)
{
  $_fv = $fv;
  if (is_callable($fv)) {
    $_fv = $_fv($data);
  }
  return $_fv;
}