<?php

use pluslib\helpers\QueryBuilding;

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
    $normalized = QueryBuilding::NormalizeColumnName($this->name);
    return $normalized . ($this->alias ? ' as ' . QueryBuilding::NormalizeColumnName($this->alias) : '');
  }
  public readonly string $name;
  public readonly string|null $alias;
  public function __construct(
    public readonly Sql_DB $db,
    string $name,
    string|null $alias = null
  ) {
    if (str_contains($name, ' as ')) {
      [$name, $alias] = explode(' as ', $name);
    }
    [$this->name, $this->alias] = [$name, $alias];
  }
  public function SELECT($cols = '*')
  {
    return new selectQueryCLASS($this, $cols);
  }
  public function INSERT(array $values)
  {
    return new insertQueryCLASS($this, $values);
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


include_once ('queries.oop.sql.php'); // update, delete, insert
include_once ('select.query.class.php'); // select

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
  public function AND($cond, $onall = false)
  {
    if ($onall) {
      $this->cond = '(' . $this->cond . ')';
    }
    $this->cond .= ' AND ' . sqlConditionGenerator::Stringify($cond);
    return $this;
  }
  public function OR($cond, $onall = false)
  {
    if ($onall) {
      $this->cond = '(' . $this->cond . ')';
    }
    $this->cond .= ' OR ' . sqlConditionGenerator::Stringify($cond);
    return $this;
  }
  public function Generate()
  {
    return sqlConditionGenerator::Stringify($this);
  }
  public function extra($cond, $boolean = 'AND', $onall = false)
  {
    if ($onall) {
      $this->cond = '(' . $this->cond . ')';
    }
    $this->cond .= " $boolean $cond";
  }

  public function __toString()
  {
    return self::Stringify($this);
  }

  public static function smart(string|sqlConditionGenerator $name, $operator = null, $value = null)
  {
    $instance = new self;

    if (!is_null($operator) && !is_null($operator) && !($name instanceof sqlConditionGenerator))
      $instance->AND(QueryBuilding::NormalizeColumnName($name) . ' = ' . $value);
    else {
      $instance->AND($name);
    }
    return $instance;
  }

}

class sqlRow
{
  public array $row;
  public $found = false;
  public function __construct(
    public readonly PDOStatement $stmt
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
    return new sql_abcol($cn, $this->row[$cn], $maxSize, $allowedTypes, $prefix);
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
    public readonly string $name, // Colname
    public readonly string|null $val, // ColVal
    public readonly int $ms,
    public readonly array $at,
    public readonly string $pf,
  ) {
  }
  function get_url()
  {
    return urlOfUpload($this->val);
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

include_once "helpers.php";