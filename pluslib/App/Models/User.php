<?php
namespace pluslib\App\Models;

defined('ABSPATH') || exit;

use pluslib\Eloquent\Attribute;
use pluslib\Eloquent\BaseModel;

class User extends BaseModel
{
  protected $table = 'users';
  protected $id_field = 'ID';

  protected $hidden = ['password'];

  /**
   * Returns current User
   * @return static|null
   */
  static function current()
  {
    if (!call_user_func([\pluslib\Config::$AuthClass, 'canlogin'])) {
      return optional(null);
    }
    return optional(
      static::find(
        \getCurrentUserInfo_prop(
          (new static)->id_field
        )
      )
    );
  }

  function fullname(): Attribute
  {
    return Attribute::make(get: fn() => $this->firstname . ' ' . $this->lastname);
  }
}