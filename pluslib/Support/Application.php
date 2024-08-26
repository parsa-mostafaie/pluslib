<?php
namespace pluslib\Support;

use pluslib\Auth;
use pluslib\Foundation\Container;
use pluslib\Security\Security;
use pluslib\Support\Facades\Facade;

class Application extends Container
{
  public $basepath = '';
  public $use_sha = true;
  public $upload_dir = '/uploads';
  public $hash_pass_disable = false;
  public $devmode = true;
  public $friend_origins = [];
  public $anti_xss_header = true;
  public $login_path;
  public $assets = '/assets';

  public int $regenerate_session_request_counts = 100;
  public bool $session_fingerprint = true;
  public int $session_expire_time = 3600;

  public string $auth_class = Auth::class;

  function getBasePath()
  {
    return $this->basepath;
  }

  function setBasePath($newValue)
  {
    $this->basepath = $newValue;

    return $this;
  }

  function basepath($value = null)
  {
    if (!is_null($value)) {
      $this->setBasePath($value);
    }

    return $this->getBasePath();
  }

  function db(...$args)
  {
    return tap($this, fn() => db(...$args));
  }

  public function invalidSessionRedirect($why = 'invses')
  {
    redirect(redirect(url(c_url($this->login_path)), true, gen: true) . '&why=' . $why);
  }

  function init()
  {
    Security::init();

    if (!$this->devmode) {
      // Product mode
      ini_set('display_errors', '0');
    } else {
      // Dev Mode
      ini_set('display_errors', 'On');
    }
  }

  static function configure(...$args)
  {
    $instance = (new static)->make('application');

    foreach ($args as $n => $v) {
      if (property_exists($instance, $n)) {
        $instance->$n = $v;
      }
    }

    Facade::setFacadeApplication($instance);

    return $instance;
  }
}