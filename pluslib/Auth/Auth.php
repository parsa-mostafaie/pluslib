<?php
namespace pluslib;

defined('ABSPATH') || exit;

use pluslib\MVC\Defaults\User as BaseUser;

class Auth
{
  protected static string $UserTable = BaseUser::class; // Not implemented, For Multi-db
  static function LoginWith($username, $password)
  {
    return loginWith($username, $password);
  }
  static function canLogin()
  {
    return canlogin();
  }
  static function canLoginWith($i, $p)
  {
    return canLoginWith($i, $p);
  }
  static function logout()
  {
    return signout();
  }
  static function authAdmin()
  {
    return authAdmin();
  }
}