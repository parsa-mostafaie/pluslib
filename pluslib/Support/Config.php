<?php
namespace pluslib\Support;

class Config
{
  protected $data = [];

  public function mergeWithFile($path, $name)
  {
    $data = (fn() => require $path)();

    if (!isset($this->data[$name])) {
      $this->data[$name] = [];
    }

    $this->data[$name] = array_merge($this->data[$name], $data);

    return $this;
  }

  public function mergeWithDirectory($path)
  {
    if (!$scan = scandir($path)) {
      return $this;
    }

    $scan = array_diff($scan, array('.', '..'));

    foreach ($scan as $file) {
      $file = join_paths($path, $file);

      $name = pathinfo($file, PATHINFO_FILENAME);

      $this->mergeWithFile($file, $name);
    }

    return $this;
  }

  public function get($variable = null, $default = null)
  {
    return data_get($this->data, $variable, $default);
  }
}