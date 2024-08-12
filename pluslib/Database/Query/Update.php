<?php
namespace pluslib\Database\Query;

defined('ABSPATH') || exit;

use pluslib\Database\Query\Helpers as QueryBuilding;
use pluslib\Database\Table;
use pluslib\Database\Condition;
use pluslib\Database\Query\Conditional;

class Update
{
  use Conditional;

  private array $arr = [];

  public function __construct(
    public readonly Table $table,
    Condition|string $cond
  ) {
    $this->init_condition();

    $this->where($cond);
  }

  private function toString($arr)
  {
    return join(
      ',',
      array_map(function ($k, $v) {
        return "$k = $v";
      }, array_keys($arr), $arr)
    );
  }

  public function fromArray(array $arr)
  {
    $this->Set($arr);
    return $this;
  }

  public function set(array $v)
  {
    $this->arr = array_merge($this->arr, $v);

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
    $arr = QueryBuilding::NormalizeArray($this->arr);
    if (!$arr)
      return;
    $tbl = $this->table->name();
    $v = $this->toString($arr);
    $condition = $this->condition;
    return "UPDATE $tbl SET $v WHERE $condition";
  }
}