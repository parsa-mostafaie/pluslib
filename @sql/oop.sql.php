<?php
defined('ABSPATH') || exit;

include_once 'uploadBaseColumn.php';

// OBJECTs
class Sql_DB extends PDO
{
  public function __construct(
    public readonly string $db = 'plus',
    public readonly string $username = 'root',
    public readonly string $password = '',
    public readonly string $host = 'localhost'
  ) {
    parent::__construct(
      'mysql:hostname=' . $this->host . ';dbname=' . $this->db . ';charset=utf8mb4',
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
  public function name()
  {
    return $this->name . ($this->alias ? ' as ' . $this->alias : '');
  }
  public function __construct(
    public readonly Sql_DB $db,
    public readonly string $name,
    public readonly string|null $alias = null
  ) {
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
    $this->cond = !empty($cond) ? $cond : '1 = 1';
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
  public array $row;
  public $found = false;
  public function __construct(
    public readonly PDOStatement $stmt,
    public readonly sql_Table $tbl,
    public readonly bool $imm = false
  ) {
    $t = $this->stmt->fetch(PDO::FETCH_ASSOC);
    $this->row = $t ? $t : [];
    if ($t) {
      $this->found = true;
    }
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
    if (!$this->imm) {
      $pk = $this->tbl->primaryKey();
      $pv = $this->getColumn($pk);
    } else
      $pk = $pv = null;
    return new sql_abcol($this->tbl, $cn, $this->row[$cn], $maxSize, $allowedTypes, $prefix, $this->imm, $pk, $pv);
  }
  public function __get($name)
  {
    return $this->getColumn($name);
  }
}

/**
 * !NOT RECOMMENDED TO USE!
 */
class sql_abcol
{
  public function __construct(
    public readonly Sql_Table $tbl,
    public readonly string $name, // Colname
    public readonly string|null $val, // ColVal
    public readonly int $ms,
    public readonly array $at,
    public readonly string $pf,
    public readonly bool $imm = false,
    public readonly mixed $pk = null,
    public readonly mixed $pv = null
  ) {
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
    if ($this->imm)
      throw new Exception('Cant Set Immutable asset-based column (This Column may selected from a select query with join)');
    $temp = $this->tbl->UPDATE($this->cond())->Set($this->name . " = ?")->Run([$v]);
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
      unlinkUpload($this->val);
      return $this->set('NULL');
    }

  }

  function get_img($cattrs = '', $undefined = '/default_uploads/unknown.png', $echo = false, $ue_src = true)
  {
    $purl =
      $this->get_url();
    return imageComponent($purl, $cattrs, $undefined, $echo, $ue_src);
  }

  function has()
  {
    $_purl = $this->get_url();
    $purl = $_SERVER['DOCUMENT_ROOT'] . regular_url($_purl);
    return file_exists($purl) && $this->get_url();
  }
}