<?php
namespace pluslib\Database;

defined('ABSPATH') || exit;

use pluslib\Database\Query\Helpers as QueryBuilding;

class Condition
{
  public static function TextSearch($searchInput, ...$cols)
  {
    $OBJ = new static();
    $qm = 0; //? question Mark Count
    $sval = ''; //? What to Like

    if ($searchInput && $searchInput != '') {
      $sval = '%' . $searchInput . '%';
      $COBJ = new static('0 = 1');
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
    if ($val instanceof static) {
      return $val;
    } else {
      return new static($val);
    }
  }

  public static function Stringify($val)
  {
    if ($val instanceof static) {
      return $val->cond;
    } else {
      return $val;
    }
  }
  public function __construct($cond = '1 = 1')
  {
    if ($cond instanceof static) {
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
    $this->cond .= ' AND ' . static::Stringify($cond);
    return $this;
  }
  public function OR($cond, $onall = false)
  {
    if ($onall) {
      $this->cond = '(' . $this->cond . ')';
    }
    $this->cond .= ' OR ' . static::Stringify($cond);
    return $this;
  }
  public function Generate()
  {
    return static::Stringify($this);
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
    return static::Stringify($this);
  }

  public static function smart(string|self $name, $operator = null, $value = null)
  {
    $instance = new static;

    if (!is_null($operator) && !is_null($operator) && !($name instanceof static))
      $instance->AND(QueryBuilding::NormalizeColumnName($name) . ' = ' . $value);
    else {
      $instance->AND($name);
    }
    return $instance;
  }

}