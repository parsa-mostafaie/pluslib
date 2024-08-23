<?php
namespace pluslib\Support\Facades;

class Facade
{
  protected $class;
  protected $accessor;
  protected static $singleton = [];

  public static function singleton(...$args)
  {
    $class = (new static)->class;
    $accessor = (new static)->accessor;

    if (empty(static::$singleton[$accessor])) {
      static::$singleton[$accessor] = new $class(...$args);
    }

    return static::$singleton[$accessor];
  }

  public static function singleton_of($accessor)
  {
    return static::$singleton[$accessor] ?? null;
  }

  public static function __callStatic($method, $args)
  {
    return static::singleton()->$method(...$args);
  }
}