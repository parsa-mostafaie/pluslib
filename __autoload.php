<?php
spl_autoload_register(function ($class) {
  if (!str_starts_with(strtolower($class), 'pluslib')) {
    return; // not pluslib
  }

  $classPath = __DIR__ . "/$class.php";
  $classPathDefault = __DIR__ . "/$class/" . basename($class) . ".php";
  $classPath = regular_url($classPath);

  if (file_exists($classPath)) {
    include_once $classPath;
  }else if(file_exists($classPathDefault)){
    include_once $classPathDefault;
  } else {
    //TODO: Show error in custom handler
  }
});