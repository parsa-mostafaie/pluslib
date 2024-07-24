<?php
namespace pluslib\App\Models;

defined('ABSPATH') || exit;

use pluslib\Eloquent\BaseModel;

class User extends BaseModel
{
  protected $table = 'users';
  protected $id_field = 'ID';

  static function current()
  {
    if (!call_user_func([\pluslib\Config::$AuthClass, 'canlogin'])) {
      return optional(null);
    }
    return optional(static::find(
      \getCurrentUserInfo_prop(
        (new static)->id_field
      )
    )
    );
  }
}