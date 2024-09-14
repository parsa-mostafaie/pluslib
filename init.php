<?php
/**
 * pluslib [BETA]: init.php v0.0.04
 * 
 * @author Parsa Mostafaie <pmostafaie1390@gmail.com>
 * @copyright 2024 Parsa Mostafaie
 * @license MIT
 * @requires PHP 8.2.12 + APACHE
 */

const ABSPATH = __DIR__ . '/';

// AutoLoad: Fix #2
include_once '__autoload.php';
include_once 'pluslib/@helpers.php';

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

define('RELPATH', abs2rel($_SERVER['DOCUMENT_ROOT'], ABSPATH));