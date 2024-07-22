<?php
namespace pluslib;

use pluslib\Auth;
use pluslib\Security\Security;

defined('ABSPATH') || exit;

class Config
{
  public static bool $passwordHashDisable = false;
  public static string $uploadDirectory = 'uploads/';
  public static bool $passwordHash_SHA256 = true;
  public static bool $devMode = true;
  public static array $allowedOrigins = [];

  public static bool $anti_xss_header = true;

  public static string $login_page;

  public static int $regenerate_session_request_counts = 100;
  public static bool $session_fingerprint = true;
  public static int $session_expire_time = 3600;

  public static string $AuthClass = Auth::class;

  public static function invalidSessionRedirect($why = 'invses')
  {
    redirect(redirect(static::$login_page, true, gen: true) . '&why=' . $why);
  }

  public static function init()
  {
    Security::init();

    if (!static::$devMode) {
      // Product mode
      ini_set('display_errors', '0');
    } else {
      // Dev Mode
      ini_set('display_errors', 'On');
    }
  }
}