<?php
namespace pluslib\Eloquent;

class Attribute
{
  protected $getter, $setter;

  public static function make(?callable $get = null, ?callable $set = null)
  {
    $instance = new static;

    $instance->getter = $get;
    $instance->setter = $set;

    return $instance;
  }

  public function get()
  {
    if ($this->hasGetter())
      return ($this->getter)();

    return null;
  }

  public function hasSetter()
  {
    return !!$this->setter;
  }

  public function hasGetter()
  {
    return !!$this->getter;
  }

  public function set($value)
  {
    if ($this->hasSetter()) {
      return ($this->setter)($value);
    }

    return null;
  }
}