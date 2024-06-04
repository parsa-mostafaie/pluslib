<?php
include_once __DIR__ . '/../init.php';
class selectQueryCLASS
{
  private Sql_Table $table;
  private $cols = '*', $join_tbl = [], $join_query = [], $condition = '1 = 1', $groupby = null, $having = null, $order
    = null, $lim = null, $p = [];

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

  public function __construct($table, $cols)
  {
    $this->table = $table;
    $this->cols = $cols;
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
    return new sqlRow($this->Run($params), $this->table, $this->join_query || $this->join_tbl);
  }

  public function Generate()
  {
    $join = $this->injoins();
    $cond = $this->condition ? "WHERE " . $this->condition : '';
    $gb = $this->groupby ? "GROUP BY " . $this->groupby : '';
    $having = $this->having ? "HAVING " . $this->having : '';
    $ob = $this->order ? "ORDER BY " . $this->order : '';
    $lm = $this->lim ? "LIMIT " . $this->lim : '';
    $cols = $this->cols;
    $tbl = $this->table->name();

    $query = "SELECT $cols FROM $tbl $join $cond $gb $having $ob $lm";
    return $query;
  }
}

class insertQueryCLASS
{
  private Sql_Table $table;
  private $keys, $vals;

  public function __construct($table, $keys)
  {
    $this->table = $table;
    $this->keys = $keys;
  }

  public function VALUES($vals)
  {
    $this->vals = $vals;
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
    return "INSERT INTO $tbl (" . $this->keys . ") VALUES ( " . $this->vals . " )";
  }
}

class updateQueryCLASS
{
  private Sql_Table $table;
  private $cond, $vals;

  public function __construct($table, $cond)
  {
    $this->table = $table;
    $this->cond = $cond;
  }

  public function WHERE($cond)
  {
    $this->cond .= ' AND ' . $cond;

    return $this;
  }

  public function SET($v)
  {
    if ($this->vals) {
      $this->vals .= ', ';
    }

    $this->vals .= $v;

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
    $v = $this->vals;
    $condition = $this->cond;
    return "UPDATE $tbl SET $v WHERE $condition";
  }
}


class deleteQueryCLASS
{
  private Sql_Table $table;
  private $condition = '1=1';

  public function __construct($table)
  {
    $this->table = $table;
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