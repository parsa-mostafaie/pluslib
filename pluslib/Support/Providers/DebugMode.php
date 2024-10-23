<?php
namespace pluslib\Support\Providers;

use pluslib\Support\ServiceProvider;

class DebugMode extends ServiceProvider
{
  public function register()
  {
    //
  }

  public function boot()
  {
    if ($this->app->isDebug()) {
      error_reporting(E_ALL);
      ini_set('display_errors', 1);
    } else {
      error_reporting(0);
      ini_set('display_errors', 0);
      ini_set('log_errors', 1);
      ini_set('error_log', storage_path("logs/errors.log"));
    }
  }
}