<?php
namespace pluslib\Database\Query;

defined('ABSPATH') || exit;

use pluslib\Database\Table;
use pluslib\Database\Deprecated\sqlRow;
use pluslib\Database\Query\Conditional;
use \PDO;

class Select extends Conditional
{
  private $join_tbl = [], $join_query = [], $groupby = null, $having = null, $order
    = null, $lim = null, $p = [];


  private array $cols = [];

  public function alsoSelect(string|array $cols)
  {
    $cols = wrap($cols);
    array_push($this->cols, ...$cols);
    return $this;
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
    public readonly Table $table,
    string|array $cols = ['*'],
    private string|null $modelType = null
  ) {
    parent::__construct();

    $this->alsoSelect($cols);
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

  public function ORDER_BY($o, $s = "")
  {
    $this->order = $o . ($s ? " $s" : "");

    return $this;
  }

  public function LIMIT($l, $o = "")
  {
    $this->lim = $l . ($o ? " OFFSET $o" : "");

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
  public function getArray($params = [], $nosql_row = false)
  {
    if ($this->modelType) {
      $mt = $this->modelType;

      $run = $this->Run($params)->fetchAll(PDO::FETCH_COLUMN, 0);

      return array_map(fn($v) => new $mt($v), $run);
    }

    $run = $this->Run($params)->fetchAll(PDO::FETCH_ASSOC);
    if ($nosql_row) {
      return $run;
    }
    return array_map(fn($v) => new sqlRow($v), $run);
  }

  public function get($params = [])
  {
    return collect($this->getArray($params));
  }

  public function first($params = [])
  {
    return $this->getArray($params)[0];
  }

  public function last($params = [])
  {
    $res = $this->getArray($params);
    return end($res);
  }

  public function count($params = [])
  {
    $clone = clone $this;

    $clone->cols = ['COUNT(*)'];

    return $clone->Run($params)->fetchColumn();
  }

  public function getFirstRow($params = [])
  {
    return new sqlRow($this->Run($params));
  }

  public function toBase($clone = false)
  {
    if ($clone) {
      return (clone $this)->toBase();
    }

    $this->modelType = null;

    return $this;
  }

  public function Generate()
  {
    $join = $this->injoins();
    $cond = $this->condition ? "WHERE " . $this->condition : '';
    $gb = $this->groupby ? "GROUP BY " . $this->groupby : '';
    $having = $this->having ? "HAVING " . $this->having : '';
    $ob = $this->order ? "ORDER BY " . $this->order : '';
    $lm = $this->lim ? "LIMIT " . $this->lim : '';
    $cols = $this->modelType ? $this->table->primaryKey() : join(', ', $this->cols);
    $tbl = $this->table->name();

    $query = "SELECT $cols FROM $tbl $join $cond $gb $having $ob $lm";
    return $query;
  }
}
