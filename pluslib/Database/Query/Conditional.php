<?php
namespace pluslib\Database\Query;

use pluslib\Database\Table;
use pluslib\Database\Condition;

class Conditional
{
  protected Condition $condition;

  function __construct()
  {
    $this->condition = new Condition();
  }

  public function WHERE(Condition|string $cond, $boolean = 'AND', $onall = false)
  {
    $this->condition->extra($cond, $boolean, $onall);

    return $this;
  }

  public function SMART($name, $operator = null, $value = null, $boolean = 'AND', $onall = false)
  {
    $cond = Condition::smart($name, $operator, $value);
    return $this->where($cond, $boolean, $onall);
  }
}
