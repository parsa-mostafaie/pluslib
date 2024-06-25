<?php
use pluslib\helpers\QueryBuilding;

defined('ABSPATH') || exit;

class ConditionalQueryBASE
{
  protected sqlConditionGenerator $condition;

  function __construct()
  {
    $this->condition = new sqlConditionGenerator();
  }

  public function WHERE(sqlConditionGenerator|string $cond, $boolean = 'AND', $onall = false)
  {
    $this->condition->extra($cond, $boolean, $onall);

    return $this;
  }
}

class insertQueryCLASS
{
  private array $arr = [];

  public function __construct(
    public readonly Sql_Table $table,
    array $arr = [],
  ) {
    $this->VALUES($arr);
  }

  public function fromArray(array $arr) // ['id'=>1, ...]
  {
    $this->arr = array_merge($this->arr, $arr);

    return $this;
  }

  public function VALUES(array $vals)
  {
    $this->fromArray($vals);

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
    $tbl = $this->table->name();
    $this->arr = QueryBuilding::NormalizeArray($this->arr);
    $keys = array_keys($this->arr);
    $vals = array_values($this->arr);
    return "INSERT INTO $tbl (" . join(', ', $keys) . ") VALUES ( " . join(', ', $vals) . " )";
  }
}

class updateQueryCLASS extends ConditionalQueryBASE
{
  private array $arr = [];

  public function __construct(
    public readonly Sql_Table $table,
    sqlConditionGenerator|string $cond
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


class deleteQueryCLASS extends ConditionalQueryBASE
{
  public function __construct(public readonly Sql_Table $table)
  {
    parent::__construct();
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
    $tbl = $this->table->name();
    $condition = $this->condition;
    return "DELETE FROM $tbl WHERE $condition";
  }
}