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
      env('database.db', 'plus'),
      env('database.user', 'root'),
      env('database.pass', ''),
      env('database.host', 'localhost'),
      env('database.charset', 'utf8mb4'),
      env('database.engine', 'mysql'),
    );
  }
}