<?php

use pluslib\HTTP\Response;
use pluslib\Support\Facades\Application;
use pluslib\Support\Facades\Facade;
use pluslib\Support\Facades\View;
use pluslib\View\ViewNotFoundException;

function app($accessor = 'application')
{
  if ($accessor == 'application') {
    return Application::singleton();
  }

  return Application::make($accessor);
}

function response($body = '', $status = 200, $headers = [])
{
  return Response::from($body)->setHeaders($headers)->status($status);
}

function auth()
{
  return app('auth');
}

function view($path, $props = [])
{
  return response(
    function () use ($path, $props) {
      if ($rpath = View::resolvePath($path)) {
        ob_start();
        extract($props);
        require $rpath;
        return ob_get_clean();
      } else
        throw new ViewNotFoundException($path);
    }
  );
}