<?php
namespace pluslib\Database;

defined('ABSPATH') || exit;

use \PDO, \Exception, pluslib\Database\Table;

class DB extends PDO
{
  public function __construct(
    public readonly string $db = 'plus',
    public readonly string $username = 'root',
    public readonly string $password = '',
    public readonly string $host = 'localhost',
    public readonly string $charset = "utf8mb4"
  ) {
    parent::__construct(
      'mysql:hostname=' . $this->host . ';dbname=' . $this->db . ';charset=' . $this->charset,
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
}
