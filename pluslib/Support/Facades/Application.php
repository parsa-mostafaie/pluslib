<?php
namespace pluslib\Support\Facades;

use pluslib\Support\Application as app;

class Application extends Facade
{
  protected $class = app::class;
  protected $accessor = 'application';
}