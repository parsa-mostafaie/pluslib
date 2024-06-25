<?php
defined('ABSPATH') || exit;

class Collection
{
  private $items = array();

  //? <Start>Helpers</Start>
  protected static function checkValue($cond, $value, $strict = false, ...$params)
  {
    $condition_res = false;
    if (is_callable($cond)) {
      $condition_res = call_user_func($cond, ...$params);
    } else {
      $condition_res = static::aStrictCheck($cond, $value, $strict);
    }
    return $condition_res;
  }

  protected static function aStrictCheck($cond, $value, $strict = false)
  {
    return $strict ? $cond === $value : $cond == $value;
  }

  protected static function aStrictChecker($strict)
  {
    if ($strict)
      return fn($a, $b) => $a === $b;

    return fn($a, $b) => $a == $b;
  }

  protected static function arr_obj_at($arr_obj, $at = null)
  {
    if (is_null($at)) {
      return $arr_obj;
    }
    if ($arr_obj instanceof Collection)
      return static::arr_obj_at(uncollect($arr_obj), $at);
    else if (is_array($arr_obj))
      return $arr_obj[$at];
    else if ($arr_obj instanceof stdClass)
      return $arr_obj->{$at};
    return $arr_obj;
  }
  //? <End>Helpers</End>

  function __construct($array = [])
  {
    $this->items = $array;
  }

  public function set($value, string|int|null $key = null)
  {
    if (is_null($key)) {
      $this->items[] = $value;
    } else {
      $this->items[$key] = $value;
    }
    return $this;
  }

  public function at($key)
  {
    return $this->items[$key];
  }

  public function count()
  {
    return count($this->items);
  }

  public function all()
  {
    return $this->items;
  }

  public function deepAll()
  {
    return $this->map(fn($v) => $v instanceof Collection ? $v->deepAll() : $v)->all();
  }

  public function values()
  {
    return collect(array_values($this->items));
  }


  public function keys()
  {
    return collect(array_keys($this->items));
  }

  public static function fill($start, $count, $value)
  {
    return collect(array_fill($start, $count, $value));
  }

  public function map($callback)
  {
    $nitems = array_map(
      function ($v, $k) use ($callback) {
        return call_user_func($callback, $v, $k);
      },
      $this->values()->all(),
      $this->keys()->all()
    );
    return $this->keys()->combine(collect($nitems));
  }

  public function after($cond, $strict = false)
  {
    $result = false;
    foreach ($this->items as $k => $v) {
      if ($result) {
        return $v;
      }
      $condition_res = static::checkValue($cond, $v, $strict, $v, $k);
      if ($condition_res) {
        $result = true;
      }
    }
    return null;
  }

  public function before($cond, $strict = false)
  {
    $prev = null;
    foreach ($this->items as $k => $v) {
      $condition_res = static::checkValue($cond, $v, $strict, $v, $k);
      if ($condition_res) {
        return $prev;
      }
      $prev = $v;
    }
    return null;
  }

  public function reduce($func, $initial = null)
  {
    $result = $initial;
    foreach ($this->items as $key => $value) {
      $result = call_user_func($func, $result, $value, $key);
    }
    return $result;
  }

  public function avg($key = null)
  {
    $sum = $this->reduce(function ($sum, $value) use ($key) {
      return $sum + (!is_null($key) ? $value[$key] : $value);
    }, 0);
    return $sum / $this->count();
  }

  public function average($key = null)
  {
    return $this->avg($key);
  }

  public function chunk($size)
  {
    return collect(array_chunk($this->items, $size));
  }

  public function collapse(): Collection
  {
    $return = [];
    array_walk_recursive($this->items, function ($a) use (&$return) {
      $return[] = $a;
    });
    return collect($return);
  }

  public function combine($values)
  {
    $return = array_combine($this->items, uncollect($values));
    return collect($return);
  }

  public function merge($values)
  {
    $return = array_merge($this->items, uncollect($values));
    return collect($return);
  }

  public function concat($values)
  {
    $return = array_merge($this->values()->all(), collect($values)->values()->all());
    return collect($return);
  }

  public function entries()
  {
    return $this->map(function ($v, $k) {
      return [$k, $v];
    });
  }

  public function contains($value_pkey, $pvalue = null, $strict = false)
  {
    if (is_null($pvalue)) {
      foreach ($this->all() as $k => $v) {
        if (static::checkValue($value_pkey, $v, $strict, $v, $k)) {
          return true;
        }
      }
      return false;
    } else {
      return $this->contains(function ($v) use ($pvalue, $value_pkey, $strict) {
        $_V = static::arr_obj_at($v, $value_pkey);
        return @(static::checkValue($pvalue, $_V, $strict, $_V));
      });
    }
  }

  public function doesntContain($value, $key = null, $strict = false)
  {
    return !$this->contains($value, $key, $strict);
  }

  public function containsOneItem()
  {
    return $this->count() == 1;
  }


  public function crossJoin(...$arr)
  {
    $arrays = [$this->items];
    array_push($arrays, ...$arr);

    $res = collect([[]]);

    foreach ($arrays as $array) {
      $array = uncollect($array);
      $tmp = collect();
      foreach ($res->all() as $ri) {
        $ri = collect($ri);
        foreach ($array as $ai) {
          $tmp->set($ri->merge([$ai]));
        }
      }
      $res = $tmp;
    }

    return $res;
  }

  public function dd()
  {
    return dd($this->deepall());
  }

  public function dump()
  {
    return dd($this->deepall());
  }


  public function diff($coll)
  {
    return collect(array_diff($this->items, uncollect($coll)));
  }

  public function diffAssoc($coll)
  {
    return collect(array_diff_assoc($this->items, uncollect($coll)));
  }
  public function diffAssocUsing($coll, $keyC)
  {
    return collect(array_diff_uassoc($this->items, uncollect($coll), $keyC));
  }
  public function diffKeys($coll)
  {
    return collect(array_diff_key($this->items, uncollect($coll)));
  }

  public function dot($context = '')
  {
    return collect(array_dot(uncollect($this), $context));
  }

  public function first()
  {
    return $this->items[0];
  }

  public function last()
  {
    return $this->items[$this->count() - 1];
  }

  public function isNotEmpty()
  {
    return $this->count() != 0;
  }

  public function shift()
  {
    return array_shift($this->items);
  }

  public function each($callback)
  {
    foreach ($this->items as $k => $v) {
      if (call_user_func($callback, $v, $k) === false)
        break;
    }
    return $this;
  }

  public function eachSpread($callback)
  {
    foreach ($this->items as $k => $v) {
      if (call_user_func($callback, ...[...(is_array($v)?$v:[$v]), $k]) === false)
        break;
    }
    return $this;
  }
}

function collect(array|Collection $array = []): Collection
{
  if ($array instanceof Collection) {
    return $array;
  }
  return new Collection($array);
}

function uncollect(array|Collection $array)
{
  if (is_array($array)) {
    return $array;
  }
  return $array->all();
}