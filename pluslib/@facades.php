<?php
use pluslib\Support\Facades\Facade;

function app($accessor = 'application')
{
  return Facade::singleton_of($accessor);
}

function HOME_URL($set = null)
{
  return app()->basepath($set);
}
