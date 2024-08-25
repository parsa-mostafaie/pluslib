<?php

use pluslib\Support\Facades\Application;
use pluslib\Support\Facades\Facade;

function app($accessor = 'application')
{
  if ($accessor == 'application') {
    return Application::singleton();
  }

  return Facade::singleton_of($accessor);
}

function HOME_URL($set = null)
{
  return app()->basepath($set);
}
