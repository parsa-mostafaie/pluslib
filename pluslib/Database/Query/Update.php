<?php
namespace pluslib\Database\Query;

defined('ABSPATH') || exit;

use pluslib\Database\Query\Helpers as QueryBuilding;
use pluslib\Database\Table;
use pluslib\Database\Condition;
use pluslib\Database\Query\Conditional;

class Update extends Conditional
{
  private array $arr = [];

  public function __construct(
    public readonly Table $table,
    Condition|string $cond
  ) {
    parent::__construct();
    $this->WHERE($cond);
  }

  private function toString()
  {
    return join(
      ',',
      array_map(function ($k, $v) {
        return "$k = $v";
      }, array_keys($this->arr), $this->arr)
    );
  }

  public function fromArray(array $arr)
  {
    $this->Set($arr);
    return $this;
  }

  public function SET(array $v)
  {
    $this->arr = array_merge($this->arr, $v);

    return $this;
  }

  public function Run($params = [])
  {
    return $this->table->db->execute_q(
      $this->Generate(),
      $params
    );
  }

  public function Generate()
  {
    $this->arr = QueryBuilding::NormalizeArray($this->arr);
    $tbl = $this->table->name();
    $v = $this->toString();
    $condition = $this->condition;
    return "UPDATE $tbl SET $v WHERE $condition";
  }
}