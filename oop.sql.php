<?php
include_once 'init.php';
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
    parent::__construct(
      'mysql:hostname=' . $this->host . ';dbname=' . $this->db . ';charset=utf8',
      $this->username,
      $this->password
    );
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

  public function TABLE($name, $nocheck = false, $alias = null)
  {
    return $this->hasTable($name) || $nocheck ? new Sql_Table($this, $name, $alias) : null;
  }
}

class Sql_Table
{
  readonly Sql_DB $db;
  private readonly string $name;
  public function name()
  {
    return $this->name . ($this->alias ? ' as ' . $this->alias : '');
  }
  readonly string $alias;
  public function __construct($db, $name, $alias = null)
  {
    $this->db = $db;
    $this->name = $name;
    $this->alias = $alias ? $alias : '';
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

include_once ('queries.oop.sql.php');

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