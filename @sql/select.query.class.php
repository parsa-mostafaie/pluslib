<?php
defined('ABSPATH') || exit;

class selectQueryCLASS extends ConditionalQueryBASE
{
  private $join_tbl = [], $join_query = [], $groupby = null, $having = null, $order
    = null, $lim = null, $p = [];


  private array $cols = [];

  public function alsoSelect(string|array $cols)
  {
    if (($this->cols != [] || ($cols != ['*'] && $cols != '*')) && $this->modelType) {
      echo ('<p style="direction: ltr"><b style="color: #fe3">Pluslib Warning!</b> selectQueryCLASS::alsoSelect is not recommended in models may cause unwanted bug!, at now if you use Run(...) or generate(...) it selects everyhting not what you want! (If you know how to prevent from unwanted bugs please share it with us in <a href="https://github.com/parsa-mostafaie/pluslib/">Github: Pluslib</a>) <i>If you are a user not admin or developer share this warning with site\'s admin!</i></p>');
      return $this;
    }
    if (!is_array($cols)) {
      $cols = [$cols];
    }
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
    public readonly Sql_Table $table,
    string|array $cols = ['*'],
    private readonly string|null $modelType = null
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
  public function getArray($params = [], $nosql_row = false)
  {
    $run = $this->Run($params)->fetchAll(PDO::FETCH_ASSOC);
    if ($this->modelType) {
      $mt = $this->modelType;
      return array_map(function ($v) use ($mt) {
        $modelInstance = new $mt();

        $modelInstance->fromArray($v);

        return $modelInstance;
      }, $run);
    }
    return array_map(function ($v) use ($nosql_row) {
      return $nosql_row ? $v : new SqlRow($v);
    }, $run);
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
    $cols = join(', ', $this->cols);
    $tbl = $this->table->name();

    $query = "SELECT $cols FROM $tbl $join $cond $gb $having $ob $lm";
    return $query;
  }
}
