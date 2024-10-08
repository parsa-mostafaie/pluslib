<?php
namespace pluslib\Database;

use pluslib\Database\Query\Helpers as QueryBuilding;

class Condition
{
  const True = "1 = 1";
  const False = "1 = 0";
  const operators = [
    '=',
    '<',
    '>',
    '<=',
    '>=',
    '<>',
    '!=',
    '<=>',
    'like',
    'like binary',
    'not like',
    'ilike',
    '&',
    '|',
    '^',
    '<<',
    '>>',
    '&~',
    'is',
    'is not',
    'rlike',
    'not rlike',
    'regexp',
    'not regexp',
    '~',
    '~*',
    '!~',
    '!~*',
    'similar to',
    'not similar to',
    'not ilike',
    '~~*',
    '!~~*',
  ];

  public static function invalidOperator($operator)
  {
    return !is_string($operator) || !in_array(strtolower($operator), static::operators, true);
  }

  protected ?string $cond = null;

  // Construct
  public function __construct($cond = null, $operator = null, $value = null)
  {
    if ($cond instanceof static) {
      $this->cond = $cond;
      return;
    }

    if ($cond instanceof \Closure) {
      $this->cond = static::True;
      $cond($this);
      return;
    }

    if (is_array($cond)) {
      foreach ($cond as $key => $item) {
        if (!is_numeric($key)) {
          $this->and(new static($key, $item));
          continue;
        }
        $this->and(new static(...(wrap($item))));
      }
      return;
    }

    if (static::invalidOperator($operator) && !is_null($operator)) {
      [$value, $operator] = [$operator, "="];
    }

    if (!is_null($operator)) {
      $this->cond = escape_col($cond) . " $operator " . escape($value);
      return;
    }

    $this->cond = !empty($cond) ? (string) $cond : (is_null($cond) ? null : static::False);
  }

  // Operator
  public function and($cond, $operator = null, $value = null)
  {
    return $this->extra($cond, $operator, $value, 'and');
  }
  public function or($cond, $operator = null, $value = null)
  {
    return $this->extra($cond, $operator, $value, 'or');
  }
  public function reverse()
  {
    return cond("NOT ($this)");
  }
  public function extra($cond, $operator = null, $value = null, $boolean = 'and')
  {
    $cond = new static(...[$cond, $operator, $value]);

    if ($this->cond)
      $this->cond .= " $boolean ($cond)";
    else
      $this->cond = (string) $cond;

    return $this;
  }

  // Convert
  public function __toString()
  {
    return $this->cond ?? static::True;
  }

}