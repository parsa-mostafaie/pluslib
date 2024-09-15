<?php
namespace pluslib\Database;

use pluslib\Database\Query\Select;
use pluslib\Database\Query\Insert;
use pluslib\Database\Query\Delete;
use pluslib\Database\Query\Update;
use pluslib\Database\Query\Helpers as QueryBuilding;

class Table
{
  public function name()
  {
    return QueryBuilding::NormalizeTableName($this->name, $this->alias);
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
  public function select($cols = '*')
  {
    return new Select($this, $cols);
  }
  public function insert(array $values = [])
  {
    return new Insert($this, $values);
  }
  public function delete($cond = "1 = 1", $operator = null, $value = null)
  {
    return (new Delete($this))->Where(cond(...func_get_args()));
  }
  public function update($cond = "1 = 1", $operator = null, $value = null)
  {
    return new Update($this, cond(...func_get_args()));
  }

  public function primaryKey()
  {
    if (!$this->db->hasTable($this->name)) {
      return NULL;
    }

    $n = $this->name;
    $q = "SELECT COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_NAME = '$n'
AND CONSTRAINT_NAME = 'PRIMARY'";
    return $this->db->execute_q($q, [], true)->fetchColumn();
  }
}