<?php
namespace pluslib\Support\Providers;
use pluslib\Support\ServiceProvider;

class Database extends ServiceProvider
{
  public function register()
  {
    //
  }

  public function boot()
  {
    $this->app->db(
      config('database.connection.db'),
      config('database.connection.user', 'root'),
      config('database.connection.pass', ''),
      config('database.connection.host', 'localhost'),
      config('database.connection.charset', 'utf8mb4'),
      config('database.connection.engine', 'mysql'),
    );
  }
}