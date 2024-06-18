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

  public function ORDER_BY($o)
  {
    $this->order = $o;

    return $this;
  }

  public function LIMIT($l)
  {
    $this->lim = $l;

    return $this;
  }

  public function Run($params = [])
  {
    $this->p = $params;

    return $this->table->db->execute_q(
      $this->Generate(),
      $this->p,
      true
    );
  }

  public function getFirstRow($params = [])
  {
    return new sqlRow($this->Run($params));
  }

  public function Generate()
  {
    $join = $this->injoins();
    $cond = $this->condition ? "WHERE " . $this->condition : '';
    $gb = $this->groupby ? "GROUP BY " . $this->groupby : '';
    $having = $this->having ? "HAVING " . $this->having : '';
    $ob = $this->order ? "ORDER BY " . $this->order : '';
    $lm = $this->lim ? "LIMIT " . $this->lim : '';
    $cols = is_array($this->cols) ? join(', ', $this->cols) : $this->cols;
    $tbl = $this->table->name();

    $query = "SELECT $cols FROM $tbl $join $cond $gb $having $ob $lm";
    return $query;
  }
}

class insertQueryCLASS
{
  private array $vals = [];
  private array $keys = [];

  public function __construct(
    public readonly Sql_Table $table,
    string|array $keys = []
  ) {
    $this->keys = is_array($keys) ? $keys : [$keys];
  }

  public function fromArray(array $arr) // ['id'=>1, ...]
  {
    $keys = array_keys($arr);
    $vals = array_values($arr);

    array_push($this->keys, ...$keys);
    array_push($this->vals, ...$vals);

    return $this;
  }

  public function VALUES(string|array $vals)
  {
    $j = is_array($vals) ? $vals : [$vals];

    array_push($this->vals, ...$j);

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
    return "INSERT INTO $tbl (" . join(', ', $this->keys) . ") VALUES ( " . join(', ', $this->vals) . " )";
  }
}

class updateQueryCLASS
{
  private array $arr = [];

  public function __construct(
    public readonly Sql_Table $table,
    public readonly string $cond
  ) {
  }

  public function WHERE($cond)
  {
    $this->cond .= ' AND ' . $cond;

    return $this;
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
    $condition = $this->cond;
    return "UPDATE $tbl SET $v WHERE $condition";
  }
}


class deleteQueryCLASS
{
  private $condition = '1=1';

  public function __construct(public readonly Sql_Table $table)
  {
  }

  public function Run($params = [])
  {
    return $this->table->db->execute_q(
      $this->Generate(),
      $params
    );
  }

  public function WHERE($cond)
  {
    $this->condition .= ' AND ' . $cond;

    return $this;
  }

  public function Generate()
  {
    $tbl = $this->table->name();
    $condition = $this->condition;
    return "DELETE FROM $tbl WHERE $condition";
  }
}