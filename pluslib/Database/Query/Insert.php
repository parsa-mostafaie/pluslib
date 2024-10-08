<?php
namespace pluslib\Database\Query;

use pluslib\Database\Query\Helpers as QueryBuilding;
use pluslib\Database\Table;

class Insert
{
  private array $arr = [];

  public function __construct(
    public readonly Table $table,
    array $arr = [],
  ) {
    $this->VALUES($arr);
  }

  public function fromArray(array $arr) // ['id'=>1, ...]
  {
    $this->arr = array_merge($this->arr, $arr);

    return $this;
  }

  public function values(array $vals)
  {
    $this->fromArray($vals);

    return $this;
  }

  public function Run($params = [])
  {
    if (!($q = $this->Generate())) {
      return;
    }
    
    return $this->table->db->execute_q(
      $q,
      $params
    );
  }

  public function Generate()
  {
    $tbl = $this->table->name();
    $arr = QueryBuilding::NormalizeArray($this->arr);
    if (!$arr)
      return;
    $keys = array_keys($arr);
    $vals = array_values($arr);
    return "INSERT INTO $tbl (" . join(', ', $keys) . ") VALUES ( " . join(', ', $vals) . " )";
  }
}
