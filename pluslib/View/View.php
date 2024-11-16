<?php
namespace pluslib\View;

class View
{
  protected $publishedNamespaces = [];
  protected $default = null;

  public function publishNamespace($namespace, $path, $override = true)
  {
    if ($override || !isset($this->publishedNamespaces[$namespace])) {
      $this->publishedNamespaces[$namespace] = $path;
    }
  }

  public function setPathForAppViews($path)
  {
    $this->default = $path;
  }

  public function resolvePath($viewName)
  {
    if (empty($viewName)) {
      return null;
    }

    $parts = explode('::', $viewName, 2);

    [$namespace, $view, $path] = [null, null, null];

    if (count($parts) == 2)
      [$namespace, $view] = $parts;
    else
      $view = $parts[0];

    if (!empty($namespace)) {
      if (isset($this->publishedNamespaces[$namespace])) {
        $path = join_paths($this->publishedNamespaces[$namespace], $view);
      }
    }

    if (!$path && !empty($this->default)) {
      $path = join_paths($this->default, $view);
    }

    if (!empty($path)) {
      $path = $path . '.php';

      if (file_exists($path)) {
        return $path;
      }

      return null;
    }

    return null;
  }
}