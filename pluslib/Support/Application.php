<?php
namespace pluslib\Support;

use pluslib\Auth;
use pluslib\Foundation\Container;
use pluslib\Support\Facades\Facade;

use pluslib\Database\DB;
use pluslib\Router\Router as Route;
use pluslib\Support\Traits\WithPaths;

class Application extends Container
{
  use WithPaths;
  public $devmode = true;
  public $friend_origins = [];
  public $anti_xss_header = true;
  public $login_path;
  public $assets = '/assets';
  public int $regenerate_session_request_counts = 100;
  public bool $session_fingerprint = true;
  public int $session_expire_time = 3600;
  public string $auth_class = Auth::class;

  /**
   * @var string Pluslib Version
   */
  const VERSION = '0.0.05';

  function getDefaultBindings()
  {
    return [
      'application' => static::class,
      'config' => Config::class,
      'database' => DB::class,
      'route' => Route::class
    ];
  }

  function getDefaultProviders()
  {
    return [
      Providers\Database::class,
      Providers\Security::class,
    ];
  }

  function db(...$args)
  {
    if (!empty($args) || empty($this['database'])) {
      Facade::unresolveInstance('database');
      $this['database'] = new DB(...$args);
    }

    return $this;
  }

  public function invalidSessionRedirect($why = 'invses')
  {
    redirect(url($this->login_path, ['why' => $why]), true);
  }

  function init()
  {
    if (!$this->devmode) {
      // Product mode
      ini_set('display_errors', '0');
    } else {
      // Dev Mode
      ini_set('display_errors', 'On');
    }

    set_exception_handler('pls_exception_handler');

    $this->boot();
  }

  function boot()
  {
    $this['config'] = $this['config']->mergeWithDirectory($this->config_path());

    parent::boot();
  }

  static function configure($basepath)
  {
    $instance = new static;
    $instance['application'] = $instance;

    $instance->withBasePath($basepath);

    Facade::setFacadeApplication($instance);

    return $instance;
  }

  // TODO Move to filesystem
  static function symlink($target, $link){
    if (!windows_os()) {
      return symlink($target, $link);
    }

    $mode = is_dir($target) ? 'J' : 'H';

    exec("mklink /{$mode} " . escapeshellarg($link) . ' ' . escapeshellarg($target));
  }
}