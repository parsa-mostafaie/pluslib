<?php
namespace pluslib\Security;

class Security
{
  public static function check_fingerprint()
  {
    if (app()->session_fingerprint) {
      $fp = md5($_SERVER['HTTP_USER_AGENT'] . '&' . $_SERVER['REMOTE_ADDR']);
      $fp_ses = get_session('fingerprint');
      if (empty($fp_ses))
        set_session('fingerprint', $fp);
      else if ($fp_ses != $fp && $fp_ses) {
        destroy_session();
        app()->invalidSessionRedirect('fingerprnt');
      }
    }
  }
  public static function check_request_count()
  {
    if (app()->regenerate_session_request_counts) {
      // increment and check
      if (empty($_SESSION['req_count'])) {
        $_SESSION['req_count'] = 0;
      } else if (++$_SESSION['req_count'] > app()->regenerate_session_request_counts) {
        // reset and regenerate
        $_SESSION['req_count'] = 0;
        session_regenerate_id(true);
      }
    }
  }
  public static function check_session_lastaccess()
  {
    if (app()->session_expire_time !== false) {
      $laccess = time();
      $la_s = get_session('lastaccess');
      if (empty($la_s))
        set_session('lastaccess', $laccess);
      else if ($laccess > ($la_s + app()->session_expire_time) && $la_s) {
        destroy_session();
        app()->invalidSessionRedirect('expired');
      }
    }
  }
  public static function init()
  {
    // Headers
    if (app()->anti_xss_header) {
      header("X-XSS-Protection: 1; mode=block");
    }

    static::check_fingerprint();
    static::check_request_count();
    static::check_session_lastaccess();
  }
}