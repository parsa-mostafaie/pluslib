<?php

use pluslib\HTTP\Response;
use pluslib\Support\Facades\Application;
use pluslib\Support\Facades\Facade;

function app($accessor = 'application')
{
  if ($accessor == 'application') {
    return Application::singleton();
  }

  return Application::make($accessor);
}

function response()
{
  return new Response;
}