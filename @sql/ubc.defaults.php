<?php
namespace pluslib\defaults;

use pluslib\SQL\UploadBaseColumn;

defined('ABSPATH') || exit;

class UserProfile extends UploadBaseColumn
{
  protected ?string $table = 'users';
  protected string $prefix = 'PROFILE_';
  protected string $colName = 'profile';
}

