<?php
defined('ABSPATH') || exit;

class selectQueryCLASS
{
  private $join_tbl = [], $join_query = [], $condition = '1 = 1', $groupby = null, $having = null, $order
    = null, $lim = null, $p = [];

  public function alsoSelect(string|array $cols)
  {
    if (!is_array($this->cols)) {
      $this->cols = [$this->cols];
    }
    if (!is_array($cols)) {
      $cols = [$this->cols];
    }
    $this->cols[] = $cols;
  }

  function pagination($per_page, $page, $params = [])
  {
    if ($this->lim) {
      trigger_error("Pagination Select Queries Can't be have LIMIT/OFFSET", E_USER_WARNING);
    }

    $stmt = $this->Run($params);
    $count = $stmt->rowCount();

    // Pagination Main
    $page = intval($page);

    $pages = ceil($count / $per_page);

    if ($page > $pages) {
      $page = $pages;
    }

    if ($page < 1) {
      $page = 1;
    }

    $off = ($page - 1) * $per_page;

    $copy = clone $this;

    $mn = $copy->LIMIT("$per_page OFFSET $off")->Run($params);

    return [
      'page_count' => $pages,
      'current' => $page,
      'res' => $mn,
      'result_count' => $mn->rowCount(),
      'count' => $count,
      'offset' => $off
    ];
  }

  public function __construct(
    public readonly Sql_Table $table,
    public readonly string|array $cols = '*'
  ) {
  }

  public function injoins()
  {
    $res = '';
    foreach ($this->join_tbl as $i => $t) {
      $res .= ' INNER JOIN ' . $t;
      $res .= ' ON ' . $this->join_query[$i];
    }
    return $res;
  }

  public function ON($jq, $jt = null)
  {
    array_push($this->join_tbl, $jt);
    array_push($this->join_query, $jq);
    return $this;
  }
  public function WHERE($cond)
  {
    $this->condition .= ' AND ' . $cond;

    return $this;
  }

  public function GROUP_BY($gb)
  {
    $this->groupby = $gb;

    return $this;
  }

  public function HAVING($h)
  {
    $this->having = $h;
    return $this;
  }
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