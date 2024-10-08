<?php
namespace pluslib\Support\Providers;

use pluslib\Security\Security as _Security;
use pluslib\Support\ServiceProvider;

class Security extends ServiceProvider
{
  function register()
  {
    //
  }

  function boot()
  {
    if (php_sapi_name() != 'cli')
      _Security::init();
  }
}