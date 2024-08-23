<?php
namespace pluslib\Support\Facades;

use pluslib\HTTP\RestAPI;

class Rest extends Facade
{
  protected $class = RestAPI::class;
  protected $accessor = 'rest';
}