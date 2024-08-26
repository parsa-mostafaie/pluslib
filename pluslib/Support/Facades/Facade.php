<?php
namespace pluslib\Support\Facades;

use pluslib\Support\Application;

class Facade
{
  protected static $app;
  protected $accessor;

  public static function singleton(...$args)
  {
    return static::getApp()->make(static::getFacadeAccessor(), $args);
  }

  public static function getFacadeAccessor()
  {
    return (new static)->accessor;
  }

  public static function setFacadeApplication($app)
  {
    static::$app = $app;
    return $app;
  }

  public static function getApp()
  {
    if (!static::$app) {
      static::$app = Application::configure();
    }

    return static::$app;
  }

  public static function __callStatic($method, $args)
  {
    return static::singleton()->$method(...$args);
  }
}