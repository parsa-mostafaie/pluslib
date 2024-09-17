<?php
namespace pluslib\Router;

use InvalidArgumentException;
use pluslib\Foundation\BindingException;
use pluslib\HTTP\Response;
use pluslib\Support\Traits\CallMethod;
use ReflectionFunction;

class Router
{
  use CallMethod;
  protected $routes = [];
  protected $base_path = '';

  public function setBasePath($base_path)
  {
    $this->base_path = $base_path;
    return $this;
  }
  public function normalizeURL($url)
  {
    if ($url == '' || $url == '/') {
      return '/';
    }

    return '/' . trim($url, '/') . '/';
  }

  public function getPath($path)
  {
    return $this->normalizeURL(
      web_url(
          join_paths(
            $this->base_path,
            $path
          )
      )
    );
  }

  public function getURL()
  {
    return $this->normalizeURL(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
  }

  public function getParts($url)
  {
    return array_slice(explode('/', $url), 1);
  }

  const oparam_reg = '/^{\s*\?(\w+)\s*}$/';
  const param_reg = '/^{\s*(\w+)\s*}$/';
  public function isParamSegment($segment)
  {
    return preg_match(self::param_reg, $segment);
  }

  public function isOParamSegment($segment)
  {
    return preg_match(self::oparam_reg, $segment);
  }

  public function getOParamName($segment)
  {
    if (!$this->isOParamSegment($segment)) {
      return null;
    }
    return preg_replace(self::oparam_reg, '$1', $segment);
  }

  public function getParamName($segment)
  {
    if (!$this->isParamSegment($segment)) {
      return null;
    }
    return preg_replace(self::param_reg, '$1', $segment);
  }

  public function compareRouteAndURL($route, $url = null)
  {
    $params = [];

    $urlParts = [];

    $url ??= $this->getURL();

    if (!is_array($url)) {
      $urlParts = $this->getParts($url);
    } else {
      $urlParts = $url;
    }

    $route = $this->getPath($route);
    $routeParts = $this->getParts($route);

    $ignore = false;

    $match = array_every(
      $routeParts,
      function ($segment, $i) use ($urlParts, &$ignore) {
        if ($segment == '**')
          return $ignore = true;

        if ($segment == '*' || $this->isOParamSegment($segment))
          return true;

        if (isset($urlParts[$i]))
          return $segment === $urlParts[$i] || $this->isParamSegment($segment);

        return false;
      }
    ) || $ignore;

    if ($match) {
      foreach ($routeParts as $i => $v) {
        if ($this->isOParamSegment($v) && isset($urlParts[$i]))
          $params[$this->getOParamName($v)] = $urlParts[$i];
        elseif ($this->isParamSegment($v))
          $params[$this->getParamName($v)] = $urlParts[$i];
      }
    }
    return ['match' => $match, 'params' => $params];
  }

  public function compareRoutesAndURL($url = null)
  {
    foreach ($this->routes as $name => $route) {
      if (!empty($route['method']) && !request_method($route['method'])) {
        continue;
      }
      if (($res = $this->compareRouteAndURL($route['path'], $url))['match']) {
        return ['route' => $route, 'name' => $name, 'params' => $res['params']];
      }
    }

    return null;
  }

  public function run()
  {
    if (($res = $this->compareRoutesAndURL())) {
      Response::from($this->callFunctionWithArray($res['route']['callback'], $res['params']))->send();
    } else {
      response()->status(404)->send();
    }
  }

  public function addRoute($route, $callback, $method = 'GET', $named = null)
  {
    $this->routes[] = ['path' => $this->normalizeURL($route), 'callback' => $callback, 'method' => $method];

    return $this;
  }


  function callFunctionWithArray($f, $params)
  {
    $f = $this->toCallable($f);
    $reflection = $this->getCallReflector($f);
    $parameters = $reflection->getParameters();

    $args = [];

    foreach ($parameters as $parameter) {
      $paramName = $parameter->getName();
      $paramType = $parameter->getType();

      // Check if the parameter exists in the array
      if (!array_key_exists($paramName, $params)) {
        // Check if the parameter is optional or has a default value
        if (!$parameter->isDefaultValueAvailable()) {
          if ($paramType && !$paramType->isBuiltin()) {
            $args[] = app()->make($paramType->getName());
            continue;
          }

          throw new InvalidArgumentException("Missing parameter: $paramName");
        } else {
          $args[] = $parameter->getDefaultValue();
        }

        continue; // If the parameter is optional, continue
      }


      if ($paramType && !$paramType->isBuiltin()) {
        // If the parameter type is a class
        $className = $paramType->getName();
        if ((new $className) instanceof RouteParameterable)
          $args[] = $className::fromRoute($params[$paramName]);
        else
          $args[] = app()->make($parameters[$paramName]);
      } else {
        // If the parameter type is a built-in typez
        $args[] = $params[$paramName];
      }
    }

    // Call the function with the specified arguments
    return $f(...$args);
  }

  // methods
  protected $allowed_directs = ['get', 'post', 'delete', 'put', 'head'];

  public function any($route, $callback, $named = null)
  {
    return $this->addRoute($route, $callback, null, $named);
  }

  public function __call($method, $args)
  {
    if (in_array($method, $this->allowed_directs)) {
      [$route, $callback] = $args;
      $name = null;

      if (count($args) >= 3) {
        $name = $args[2];
      }

      return $this->addRoute($route, $callback, $method, $name);
    }
  }
}