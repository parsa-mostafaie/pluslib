<?php
function pls_autoload(string|null $namespace = null, string|null $dir = null)
{
  static $structures = [];

  if (!is_null($namespace) && !is_null($dir)) {
    $structures[$namespace] = $dir;
  }

  return $structures;
}

spl_autoload_register(function ($class) {
  $structure = pls_autoload();

  foreach ($structure as $namespace => $dir) {
    if (!str_starts_with(strtolower($class), strtolower($namespace))) {
      continue;
    }


    $classPath = join_paths($dir, "/$class.php");
    $classPathDefault = join_paths($dir, $class, basename($class) . ".php");

    $classPath = regular_url($classPath);
    $classPathDefault = regular_url($classPathDefault);

    if (file_exists($classPath)) {
      include_once $classPath;
    } else if (file_exists($classPathDefault)) {
      include_once $classPathDefault;
    } else {
      continue;
    }

    return;
  }

  // NO Autoload found!
});

// Default Autoloads: Pluslib
pls_autoload('pluslib', __DIR__);