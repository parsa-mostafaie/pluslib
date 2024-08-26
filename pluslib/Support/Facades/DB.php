<?php
namespace pluslib\Support\Facades;

use pluslib\Database\DB as _DB;

class DB extends Facade
{
  protected $accessor = 'database';
}