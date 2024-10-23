<?php
namespace pluslib\Database;

use \PDO, \Exception, pluslib\Database\Table;

class DB extends PDO
{
  public function __construct(
    public readonly string $db,
    public readonly string $username = 'root',
    public readonly string $password = '',
    public readonly string $host = 'localhost',
    public readonly string $charset = "utf8mb4",
    public readonly string $engine = 'mysql'
  ) {
    parent::__construct(
      "$engine:host=$host;dbname=$db;charset=$charset",
      $this->username,
      $this->password
    );
  }
  public function execute_q($q, $p, $fetch_b = false)
  {

    $query = $this->prepare($q);

    $ex = $query->execute($p);

    return $fetch_b ? $query : $ex;
  }
  public function hasTable($name)
  {
    try {
      $_ = $this->execute_q('select 1 from `' . $name . '` LIMIT 1', []);
      return true;
    } catch (Exception) {
      return false;
    }
  }

  public function table($name, $alias = null)
  {
    return new Table($this, $name, $alias);
  }

  public function transaction(callable $callback)
  {
    try {
      $this->beginTransaction();

      $callback();

      $this->commit();
    } catch (Exception $exception) {
      $this->rollBack();

      throw $exception;
    }
  }
}
