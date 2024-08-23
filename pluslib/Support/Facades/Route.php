<?php
namespace pluslib\Support\Facades;

use pluslib\Router\Router;

class Route extends Facade
{
  protected $class = Router::class;
  protected $accessor = 'route';
}