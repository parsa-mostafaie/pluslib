<?php
namespace pluslib\MVC\Defaults;

defined('ABSPATH') || exit;

use pluslib\MVC\BaseModel;

class User extends BaseModel
{
  protected $table = 'users';
  protected $id_field = 'ID';
}