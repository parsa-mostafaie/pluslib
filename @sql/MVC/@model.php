<?php
namespace pluslib\MVC;

use Sql_DB;
use Sql_Table;

defined('ABSPATH') || exit;

class Model
{
  protected Sql_DB $connection;
  protected Sql_Table $table;
  protected string $primaryKey = 'ID';

  // protected bool $incrementing = true;

  public bool $exists = false;
  protected $id;

  public static function all($cols = ['*'])
  {
    return (new static)->table->SELECT($cols)->Run();
  }
}