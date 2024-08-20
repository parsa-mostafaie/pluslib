<?php
namespace pluslib\Router;

class Router
{
  protected $routes = [];
  protected $base_path = '';

  public function setBasePath($base_path)
  {
    $this->base_path = $base_path;
    return $this;
  }

  public function getPath($path)
  {
    return web_url(c_url($this->base_path . $path));
  }

  public function getURL()
  {
    return web_url(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
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

    if (!is_array($url)) {
      $url ??= $this->getURL();

      if (str_ends_with($url, '/')) {
        $url = str_replace("/\/$/", '', $url);
      }

      $urlParts = $this->getParts($url);
    } else {
      $urlParts = $url;
    }

    $route = $this->getPath($route);
    $routeParts = $this->getParts($route);

    $match = array_every(
      $routeParts,
      function ($segment, $i) use ($urlParts) {
        if ($segment == '*' || $this->isOParamSegment($segment))
          return true;

        if (!empty($urlParts[$i]))
          return $segment === $urlParts[$i] || $this->isParamSegment($segment);

        return false;
      }
    );

    if ($match) {
      foreach ($routeParts as $i => $v) {
        if ($this->isOParamSegment($v))
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
      if (!request_method($route['method'])) {
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
      $res['route']['callback']($res['params']);
    } else {
      _404_();
    }
  }

  public function addRoute($route, $callback, $method = 'GET', $named = null)
  {
    $this->routes[] = ['path' => $route, 'callback' => $callback, 'method' => $method];

    return $this;
  }

  // methods
  protected $allowed_directs = ['get', 'post', 'delete', 'put', 'head'];

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