<?php
namespace pluslib\Database\Query;

defined('ABSPATH') || exit;

use pluslib\Database\Table;
use pluslib\Database\Query\Conditional;
use \PDO;

class Select
{
  use Conditional;

  private $joins = [], $groupby = null, $having = null, $order
    = [], $lim = null, $p = [], $offset = null;

  private array $cols = [];

  public function __construct(
    public readonly Table $table,
    string|array $cols = ['*'],
  ) {
    $this->init_condition();

    $this->alsoSelect($cols);
  }

  public function joins()
  {
    $res = '';
    foreach ($this->joins as $q) {
      $res .= ' ' . $q['type'] . ' JOIN ' . $q['table'];
      $res .= ' ON ' . $q['on'];
    }
    return $res;
  }

  public function on($jq, $jt = null, $type = "inner")
  {
    $this->joins[] = ['type' => $type, 'on' => $jq, 'table' => $jt];
    return $this;
  }

  public function groupBy($gb)
  {
    $this->groupby = escape_col($gb);

    return $this;
  }

  public function having($h)
  {
    $this->having = $h;
    return $this;
  }

  public function orderBy($o, $s = "")
  {
    $this->order[] = escape_col($o) . ($s ? " $s" : "");

    return $this;
  }

  public function limit($l, $o = null)
  {
    $this->lim = $l;

    if ($o) {
      $this->offset($o);
    }

    return $this;
  }

  public function offset($o)
  {
    $this->offset = $o;

    return $this;
  }

  public function take($count, $offset = null)
  {
    return $this->limit($count, $offset);
  }

  public function skip($offset)
  {
    return $this->offset($offset);
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

  public function getArray($params = [])
  {
    $run = $this->Run($params)->fetchAll(PDO::FETCH_ASSOC);

    return $run;
  }

  public function get($params = [])
  {
    return collect($this->getArray($params));
  }

  public function alsoSelect($cols)
  {
    $cols = wrap($cols);
    array_push($this->cols, ...array_map('escape_col', $cols));
    return $this;
  }

  public function selectRaw($cols)
  {
    return $this->alsoSelect(array_map('expr', wrap($cols)));
  }

  function pagination($per_page, $page, $params = [])
  {
    if ($this->lim) {
      trigger_error("Pagination Select Queries Can't be have LIMIT/OFFSET", E_USER_WARNING);
    }

    $count = $this->count($params);

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

    $res_q = $copy->LIMIT($per_page, $off);
    $mn = $res_q->Run($params);

    return [
      'page_count' => $pages,
      'current' => $page,
      'res' => $mn,
      'res_q' => $res_q,
      'result' => $res_q->get($params),
      'result_count' => $res_q->count($params),
      'count' => $count,
      'offset' => $off
    ];
  }


  public function first($params = [])
  {
    $res = $this->getArray($params);
    return reset($res);
  }

  public function last($params = [])
  {
    $res = $this->getArray($params);
    return end($res);
  }

  public function count($params = [])
  {
    return $this->Run($params)->rowCount();
  }

  public function Generate()
  {
    $join = $this->joins();
    $cond = $this->condition ? "WHERE " . $this->condition : '';
    $gb = $this->groupby ? "GROUP BY " . $this->groupby : '';
    $having = $this->having ? "HAVING " . $this->having : '';
    $ob = $this->order ? "ORDER BY " . implode(', ', $this->order) : '';
    $os = $this->offset ? "OFFSET {$this->offset}" : '';
    $lm = $this->lim ? "LIMIT {$this->lim} $os" : '';
    $cols = join(', ', $this->cols);
    $tbl = $this->table->name();

    $query = "SELECT $cols FROM $tbl $join $cond $gb $having $ob $lm";
    
    return $query;
  }
}
