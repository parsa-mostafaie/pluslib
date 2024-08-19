<?php
namespace pluslib\Support\Facades;

class Facade
{
  protected static $class = "";
  protected static $singleton = null;

  public static function singleton(...$args)
  {
    if (!static::$singleton) {
      static::$singleton = new static::$class(...$args);
    }

    return static::$singleton;
  }

  public static function __callStatic($method, $args)
  {
    return static::singleton()->$method(...$args);
  }
}