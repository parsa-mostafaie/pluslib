<?php
namespace pluslib\Database\Query;

use pluslib\Database\Table;
use pluslib\Database\Condition;

trait Conditional
{
  protected Condition $condition;

  function init_condition()
  {
    $this->condition = new Condition();
  }

  public function where($cond, $operator = null, $value = null, $boolean = 'and')
  {
    $this->condition->extra($cond, $operator, $value, $boolean);

    return $this;
  }

  public function orWhere($cond, $operator = null, $value = null)
  {
    return $this->where($cond, $operator, $value, 'or');
  }

  public function whereNot($cond, $operator = null, $value = null, $boolean = 'and')
  {
    return $this->where(cond($cond, $operator, $value)->reverse(), boolean: $boolean);
  }

  public function orWhereNot($cond, $operator = null, $value = null)
  {
    return $this->whereNot($cond, $operator, $value, boolean: 'or');
  }
}
