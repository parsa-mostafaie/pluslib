<?php
namespace pluslib\Database;

defined('ABSPATH') || exit;

use pluslib\Database\Query\Select;
use pluslib\Database\Query\Insert;
use pluslib\Database\Query\Delete;
use pluslib\Database\Query\Update;
use pluslib\Database\Query\Helpers as QueryBuilding;

class Table
{
  public function name()
  {
    $normalized = QueryBuilding::NormalizeColumnName($this->name);
    return $normalized . ($this->alias ? ' as ' . QueryBuilding::NormalizeColumnName($this->alias) : '');
  }
  public readonly string $name;
  public readonly string|null $alias;
  public function __construct(
    public readonly DB $db,
    string $name,
    string|null $alias = null
  ) {
    if (str_contains($name, ' as ')) {
      [$name, $alias] = explode(' as ', $name);
    }
    [$this->name, $this->alias] = [$name, $alias];
  }
  public function SELECT($cols = '*')
  {
    return new Select($this, $cols);
  }
  public function INSERT(array $values)
  {
    return new Insert($this, $values);
  }
  public function DELETE($condition)
  {
    return (new Delete($this))->Where($condition);
  }
  public function primaryKey()
  {
    $n = $this->name;
    $q = "SELECT COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_NAME = '$n'
AND CONSTRAINT_NAME = 'PRIMARY'";
    return $this->db->execute_q($q, [], true)->fetchColumn();
  }
  public function UPDATE($condition)
  {
    return new Update($this, $condition);
  }
}