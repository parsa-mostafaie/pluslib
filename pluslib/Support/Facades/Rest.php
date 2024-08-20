<?php
namespace pluslib\Support\Facades;

use pluslib\HTTP\RestAPI;

class Rest extends Facade
{
  protected static $class = RestAPI::class;
}