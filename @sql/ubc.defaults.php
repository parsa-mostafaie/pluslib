<?php
namespace pluslib\defaults;

use pluslib\SQL\UploadBaseColumn;

defined('ABSPATH') || exit;

class UserProfile extends UploadBaseColumn
{

  function __construct()
  {
    parent::__construct();

    $this->tbl = db()->TABLE('users');
    $this->prefix = 'PROFILE_';
    $this->colName = 'profile';
  }
}

