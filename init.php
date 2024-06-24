<?php
const ABSPATH = __DIR__ . '/';

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
include_once 'config.php';

include_once '@url.php';

include_once 'array.php';

include_once '@form/processors/@processor.php';
include_once '@form/get_post.php';

include_once '@security/upload.php';
include_once '@security/security.php';

include_once 'session.php';

include_once '@sql/sql.php';
include_once '@sql/oop.sql.php';
include_once '@sql/queries.oop.sql.php';

include_once '@user/user.php';
include_once '@user/follow.php';
include_once '@user/auth.php';

include_once "info.php";

include_once '@form/input_validation.php';

include_once 'headers.php';
include_once '@ajax.php';

include_once "admin/lib.php";

include_once "helpers.php";

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