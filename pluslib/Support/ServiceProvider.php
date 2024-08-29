<?php
namespace pluslib\Support;

abstract class ServiceProvider
{
  /**
   * The application instance
   * 
   * @var Application
   */
  protected $app = null;

  public function __construct(Application $app)
  {
    $this->app = $app;
  }

  public abstract function boot();
  public abstract function register();
}