<?php
//NOTE: THIS NEED CUSTOMIZATION IN LINE THAT MARKED BY *

date_default_timezone_set('Asia/Tehran');

function db(...$args)
{
  static $db;
  if (!isset($db) || count($args) > 0) {
    // $db = new PDO('mysql:dbname=plus;charset=utf8', 'root', ''); // *
    $db = new Sql_DB(...$args);
  }
  return $db;
}

//! Only for strings search
function searchText_Condition($searchInput, ...$cols)
{
  $where = '1 = 1'; //? Conditions to find
  $qm = 0; //? question Mark Count
  $sval = ''; //? What to Like

  if ($searchInput && $searchInput != '') {
    $sval = '%' . $searchInput . '%';
    $where .= ' AND ( 0 = 1 ';
    foreach ($cols as $col) {
      $where .= " OR $col LIKE ? ";
      $qm++;
    }
    $where .= ')';
  }

  return [$where, array_fill(0, $qm, $sval)];
}

function exec_q($q, $p, $fetch_b = false)
{
  $query = db()->prepare($q);

  $ex = $query->execute($p);

  return $fetch_b ? $query : $ex;
}


function insert_q($tbl, $keys, $vals, $params)
{
  return exec_q(
    "INSERT INTO $tbl ($keys) VALUES ( $vals )",
    $params
  );
}

// SELECT $cols FROM $tbl INNER JOIN $join_tbl ON $join_query WHERE $condition GROUP BY $groupby HAVING $having ORDER BY $order LIMIT $lim
function select_q($tbl, $cols, $join_tbl = null, $join_query = null, $condition = null, $groupby = null, $having = null, $order = null, $lim = null, $p = [])
{
  $join = $join_tbl && $join_query ? "INNER JOIN" . $join_tbl . " ON $join_query" : '';
  $cond = $condition ? "WHERE $condition" : '';
  $gb = $groupby ? "GROUP BY $groupby" : '';
  $having = $having ? "HAVING $having" : '';
  $ob = $order ? "ORDER BY $order" : '';
  $lm = $lim ? "LIMIT $lim" : '';

  $query = "SELECT $cols FROM $tbl $join $cond $gb $having $ob $lm";

  return exec_q(
    $query,
    $p,
    true
  );
}

function delete_q($tbl, $condition, $params = [])
{
  return exec_q("DELETE FROM $tbl WHERE $condition", $params);
}

function update_q($tbl, $condition, $set, $params = [])
{
  return exec_q("UPDATE $tbl SET $set WHERE $condition", $params);
}

// Pagination
function PaginationQuery($per_page, $page, $fetchMode, ...$SEL_PARAMS)
{
  $SEL = $SEL_PARAMS;

  $SEL['cols'] = 'COUNT(*)';

  $count = select_q(...$SEL)->fetchColumn(0);

  // Pagination Main
  $page = intval($page);

  $pages = ceil($count / $per_page);

  if ($page < 1) {
    $page = 1;
  }

  if ($page > $pages) {
    $page = $pages;
  }

  $off = ($page - 1) * $per_page;

  $SEL_PARAMS['lim'] = "$per_page OFFSET $off";

  $mn = select_q(...$SEL_PARAMS);

  return ['page_count' => $pages, 'res' => $mn->fetchAll($fetchMode), 'current' => $page, 'count' => $count, 'offset' => $off];
}


// OBJECTs
class Sql_DB extends PDO
{
  private $username;
  private $password;
  private $db;
  private $host;
  public function __construct($db = 'plus', $username = 'root', $password = '', $host = 'localhost')
  {
    $this->db = $db;
    $this->username = $username;
    $this->password = $password;
    $this->host = $host;
    parent::__construct('mysql:hostname=' . $this->host . ';dbname=' . $this->db . ';charset=utf8', $this->username, $this->password);
  }
  public function execute_q($q, $p, $fetch_b = false)
  {

    $query = $this->prepare($q);

    $ex = $query->execute($p);

    return $fetch_b ? $query : $ex;
  }
  public function hasTable($name)
  {
    try {
      $_ = $this->execute_q('select 1 from `' . $name . '` LIMIT 1', []);
      return true;
    } catch (Exception) {
      return false;
    }
  }

  public function TABLE($name, $nocheck = false)
  {
    return $this->hasTable($name) || $nocheck ? new Sql_Table($this, $name) : null;
  }
}

class Sql_Table
{
  readonly Sql_DB $db;
  readonly string $name;
  public function __construct($db, $name)
  {
    $this->db = $db;
    $this->name = $name;
  }
  public function SELECT($cols = '*')
  {
    return new selectQueryCLASS($this, $cols);
  }
  public function INSERT($keys)
  {
    return new insertQueryCLASS($this, $keys);
  }
  public function DELETE($condition)
  {
    return (new deleteQueryCLASS($this))->Where($condition);
  }
  public function primaryKey()
  {
    $n = $this->name;
    $q = "SELECT COLUMN_NAME 
FROM information_schema.KEY_COLUMN_USAGE 
WHERE TABLE_NAME = '$n' 
  AND CONSTRAINT_NAME = 'PRIMARY'";
    return $this->db->execute_q($q, [], true)->fetchColumn();
  }
  public function UPDATE($condition)
  {
    return new updateQueryCLASS($this, $condition);
  }
}

class selectQueryCLASS
{
  private Sql_Table $table;
  private $cols = '*', $join_tbl = null, $join_query = null, $condition = '1 = 1', $groupby = null, $having = null, $order = null, $lim = null, $p = [];

  public function __construct($table, $cols)
  {
    $this->table = $table;
    $this->cols = $cols;
  }

  public function INNER_JOIN($jt)
  {
    $this->join_tbl = $jt;
    return $this;
  }
  public function injoins()
  {
    if (is_array($this->join_tbl) || is_array($this->join_query)) {
      $res = '';
      foreach ($this->join_tbl as $i => $t) {
        $res .= ' INNER JOIN ' . $t;
        $res .= ' ON ' . $this->join_query[$i];
      }
      return $res;
    } else {
      $join = $this->join_tbl && $this->join_query ? "INNER JOIN " . $this->join_tbl . " ON " . $this->join_query : '';
      return $join;
    }
  }

  private static function set_arr_s(&$arr_s, $v)
  {
    if ($arr_s) {
      if (!is_array($arr_s))
        $arr_s = [$arr_s];

      array_push($arr_s, $v);
    } else
      $arr_s = $v;
    return $arr_s;
  }

  public function ON($jq, $jt = null)
  {
    if ($this->join_tbl === null || $jt) {
      selectQueryCLASS::set_arr_s($this->join_tbl, $jt);
    }
    selectQueryCLASS::set_arr_s($this->join_query, $jq);
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
    return new sqlRow($this->Run($params), $this->table);
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
    $tbl = $this->table->name;

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
    $tbl = $this->table->name;
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
    $tbl = $this->table->name;
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
    $tbl = $this->table->name;
    $condition = $this->condition;
    return "DELETE FROM $tbl WHERE $condition";
  }
}

class sqlConditionGenerator
{
  public static function TextSearch($searchInput, ...$cols)
  {
    $OBJ = new sqlConditionGenerator();
    $qm = 0; //? question Mark Count
    $sval = ''; //? What to Like

    if ($searchInput && $searchInput != '') {
      $sval = '%' . $searchInput . '%';
      $COBJ = new sqlConditionGenerator('0 = 1');
      foreach ($cols as $col) {
        $COBJ->OR(" $col LIKE ? ");
      }
      $OBJ->AND(' ( ' . $COBJ->Generate() . ' ) ');
      $qm = count($cols);
    }

    return [$OBJ->Generate(), array_fill(0, $qm, $sval)];
  }
  private $cond;
  public static function Objectify($val)
  {
    if ($val instanceof sqlConditionGenerator) {
      return $val;
    } else {
      return new sqlConditionGenerator($val);
    }
  }

  public static function Stringify($val)
  {
    if ($val instanceof sqlConditionGenerator) {
      return $val->cond;
    } else {
      return $val;
    }
  }
  public function __construct($cond = '1 = 1')
  {
    if ($cond instanceof sqlConditionGenerator) {
      $this->cond = $cond->cond;
      return;
    }
    $this->cond = $cond && (strlen($cond) > 0) ? $cond : '1 = 1';
  }
  public function AND($cond)
  {
    $this->cond .= ' AND ' . sqlConditionGenerator::Stringify($cond);
    return $this;
  }
  public function OR($cond)
  {
    $this->cond .= ' OR ' . sqlConditionGenerator::Stringify($cond);
    return $this;
  }
  public function Generate()
  {
    return sqlConditionGenerator::Stringify($this);
  }
}

class sqlRow
{
  public PDOStatement $stmt;
  public array $row;
  public ?Sql_Table $tbl = null;
  public $found = false;
  public function __construct(PDOStatement $stmt, $tbl)
  {
    $this->stmt = $stmt;
    $t = $this->stmt->fetch(PDO::FETCH_ASSOC);
    $this->row = $t ? $t : [];
    if ($t) {
      $this->found = true;
    }
    $this->tbl = $tbl;
  }
  public function getColumn($cn)
  {
    return $this->row[$cn];
  }
  public function getAssetBasedCol(
    $cn,
    $maxSize = 3145728,
    $allowedTypes = [
      'image/png' => 'png',
      'image/jpeg' => 'jpg'
    ],
    $prefix = ''
  ) {
    $pk = $this->tbl->primaryKey();
    $pv = $this->getColumn($pk);
    return new sql_abcol($this->tbl, $cn, $this->row[$cn], $maxSize, $allowedTypes, $prefix, $pk, $pv);
  }
}

class sql_abcol
{
  public $val, $name;
  public $ms, $at, $pf;
  public $pk, $pv;
  public Sql_Table $tbl;
  public function __construct($tbl, $name, $val, $ms, $at, $p, $pk, $pv)
  {
    $this->tbl = $tbl;
    [$this->val, $this->name] = [$val, $name];
    [$this->ms, $this->at, $this->pf] = [$ms, $at, $p];
    [$this->pk, $this->pv] = [$pk, $pv];
  }
  public function cond()
  {
    return $this->pk . ' = ' . $this->pv;
  }
  public function set_inp(
    $name
  ) {
    $file = uploadFile_secure($name, $this->ms, $this->at, $this->pf);
    if ($file) {
      $this->rem();
      return $this->set($file);
    }
  }
  private function set(
    $v
  ) {
    $temp = $this->tbl->Update($this->cond())->Set($this->name = $v);
    if ($temp) {
      $this->val = $v;
    }
    return $temp;
  }
  function get_url()
  {
    return urlOfUpload($this->val);
  }

  function rem()
  {
    if ($this->has()) {
      unlinkUpload($this->get_url());
      return $this->set('NULL');
    }

  }

  function get_img($cattrs = '', $undefined = '/default_uploads/unknown.png', $echo = false, $ue_src = true)
  {
    $purl =
      $this->get_url();
    return imageComponent($purl, $cattrs);
  }

  function has()
  {
    $_purl = $this->get_url();
    $purl = $_SERVER['DOCUMENT_ROOT'] . regular_url($_purl);
    return file_exists($purl) && $this->get_url();
  }
}