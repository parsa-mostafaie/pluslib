<?php
namespace pluslib\Support\Providers;

use pluslib\Support\ServiceProvider;

class Timezone extends ServiceProvider
{
  function register()
  {
    //
  }

  function boot()
  {
    date_default_timezone_set(config('app.timezone', 'UTC'));
  }
}