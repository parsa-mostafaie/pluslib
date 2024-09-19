<?php
namespace pluslib;

use pluslib\Support\Config;

class Auth
{
  protected ?string $model = null;
  protected $session = 'auth_credentials';

  public function __construct()
  {
    $this->model = config('auth.model');
    $this->session = config('auth.session', 'auth_credentials');
  }

  public function login($credentials)
  {
    session_new_id();

    return tap($this->checkWith($credentials), function ($state) use ($credentials) {
      if ($state)
        set_session($this->session, $credentials);
    });
  }

  function check()
  {
    return $this->checkWith($this->credentials());
  }

  function checkWith($credentials)
  {
    return app()->call([$this->model, 'authenticate'], ['credentials'=>$credentials]);
  }

  function user()
  {
    $credentials = $this->credentials();

    return app()->call([$this->model, 'getAuthenticated'], ['credentials'=>$credentials]);
  }

  function credentials()
  {
    return get_session($this->session) ?: [];
  }

  function logout()
  {
    session__unset($this->session);
    session_new_id();

    return true;
  }
}