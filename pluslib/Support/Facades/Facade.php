<?php
namespace pluslib\Support\Facades;

class Facade
{
  protected static $class = "";
  protected static $singleton = [];

  public static function singleton(...$args)
  {
    if (empty(static::$singleton[static::class])) {
      static::$singleton[static::class] = new static::$class(...$args);
    }

    return static::$singleton[static::class];
  }

  public static function __callStatic($method, $args)
  {
    return static::singleton()->$method(...$args);
  }
}