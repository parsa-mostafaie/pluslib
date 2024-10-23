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

function response($body = '', $status = 200, $headers = [])
{
  return (new Response)->setBody($body)->setHeaders($headers)->status($status);
}

function auth()
{
  return app('auth');
}

function view($path, $props = [])
{
  return response(
    function () use ($path, $props) {
      ob_start();
      extract($props);
      require resources_path(join_paths('views', "$path.php"));
      return ob_get_clean();
    }
  );
}