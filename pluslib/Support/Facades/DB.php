<?php
namespace pluslib\Support\Facades;

class DB
{
  public static function __callStatic($method, $args)
  {
    return db()->$method(...$args);
  }
}