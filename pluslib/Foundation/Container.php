<?php
namespace pluslib\Foundation;

use ArrayAccess;
use Closure;
use Exception;
use ReflectionClass;
use ReflectionParameter;

class BindingException extends Exception
{
}

class Container implements ArrayAccess
{
  use CallResolver;
  /**
   * The container's bindings.
   *
   * @var array
   */
  protected $bindings = array();

  /**
   * The container's shared instances.
   *
   * @var array
   */
  protected $instances = array();

  /**
   * The registered type aliases.
   *
   * @var array
   */
  protected $aliases = array();

  /**
   * All of the registered resolving callbacks.
   *
   * @var array
   */
  protected $resolvingCallbacks = array();

  /**
   * All of the global resolving callbacks.
   *
   * @var array
   */
  protected $globalResolvingCallbacks = array();

  /**
   * List of Service Providers
   * 
   * @var array
   */
  protected $providers = [];

  /**
   * determins that application is booted?
   * 
   * @var bool
   */
  protected $booted = false;

  function getDefaultBindings()
  {
    return [];
  }

  function getDefaultProviders()
  {
    return [];
  }

  public function __construct()
  {
    foreach ($this->getDefaultBindings() as $abstract => $creator) {
      $this->bind([$creator => $abstract]);
    }

    $this->providers = $this->getDefaultProviders();
  }

  /**
   * Determine if the given abstract type has been bound.
   *
   * @param  string  $abstract
   * @return bool
   */
  public function bound($abstract)
  {
    $abstract = $this->getAlias($abstract);
    return isset($this->bindings[$abstract]) or isset($this->instances[$abstract]);
  }

  /**
   * Register a binding with the container.
   *
   * @param  string|array               $abstract
   * @param  Closure|string|null  $creator
   * @param  bool                 $shared
   * @return void
   */
  public function bind($abstract, $creator = null, $shared = false)
  {
    if (is_array($abstract)) {
      list($abstract, $alias) = $this->extractAlias($abstract);

      $this->alias($abstract, $alias);
    }

    $abstract = $this->getAlias($abstract);

    unset($this->instances[$abstract]);

    if (is_null($creator)) {
      $creator = $abstract;
    }

    if (!$creator instanceof Closure) {
      $creator = function ($c) use ($abstract, $creator) {
        $method = ($abstract == $creator) ? 'build' : 'make';

        return $c->$method($creator);
      };
    }

    $this->bindings[$abstract] = compact('creator', 'shared');
  }

  /**
   * Register a binding if it hasn't already been registered.
   *
   * @param  string               $abstract
   * @param  Closure|string|null  $creator
   * @param  bool                 $shared
   */
  public function bindIf($abstract, $creator = null, $shared = false)
  {
    if (!$this->bound($abstract)) {
      $this->bind($abstract, $creator, $shared);
    }
  }

  /**
   * Register a shared binding in the container.
   *
   * @param  string|array               $abstract
   * @param  Closure|string|null  $creator
   * @return void
   */
  public function singleton($abstract, $creator = null)
  {
    return $this->bind($abstract, $creator, true);
  }

  /**
   * Wrap a Closure such that it is shared.
   *
   * @param  Closure  $closure
   * @return Closure
   */
  public function share(Closure $closure)
  {
    return function ($container) use ($closure) {
      static $object;

      if (is_null($object)) {
        $object = $closure($container);
      }

      return $object;
    };
  }

  /**
   * "Extend" an abstract type in the container.
   *
   * @param  string   $abstract
   * @param  Closure  $closure
   * @return void
   */
  public function extend($abstract, Closure $closure)
  {
    $abstract = $this->getAlias($abstract);

    if (!$this->bound($abstract)) {
      throw new \InvalidArgumentException("Type $abstract is not bound.");
    }

    $resolver = $this->bindings[$abstract]['creator'];

    $this->bind($abstract, function ($container) use ($resolver, $closure) {
      return $closure($resolver($container), $container);
    }, $this->isShared($abstract));
  }

  /**
   * Register an existing instance as shared in the container.
   *
   * @param  string|array  $abstract
   * @param  mixed   $instance
   * @return void
   */
  public function instance($abstract, $instance)
  {
    if (is_array($abstract)) {
      [$abstract, $alias] = $this->extractAlias($abstract);

      $this->alias($abstract, $alias);
    }

    $abstract = $this->getAlias($abstract);

    $this->instances[$abstract] = $instance;
  }

  /**
   * Alias a type to a shorter name.
   *
   * @param  string  $abstract
   * @param  string  $alias
   * @return void
   */
  public function alias($abstract, $alias)
  {
    $this->aliases[$alias] = $abstract;
  }

  /**
   * Extract the type and alias from a given definition.
   *
   * @param  array  $definition
   * @return array
   */
  protected function extractAlias(array $definition)
  {
    return array(key($definition), current($definition));
  }

  /**
   * Resolve the given type from the container.
   *
   * @param  string  $abstract
   * @param  array   $parameters
   * @return mixed
   */
  public function make($abstract, $parameters = array())
  {
    $abstract = $this->getAlias($abstract);

    $needsBuild = !empty($parameters);

    if (isset($this->instances[$abstract]) && !$needsBuild) {
      return $this->instances[$abstract];
    }

    $creator = $this->getCreator($abstract);

    if ($this->isBuildable($creator, $abstract)) {
      $object = $this->build($creator, $parameters);
    } else {
      $object = $this->make($creator, $parameters);
    }

    if ($this->isShared($abstract)) {
      $this->instances[$abstract] = $object;
    }

    $this->fireResolvingCallbacks($abstract, $object);

    return $object;
  }

  /**
   * Get the creator type for a given abstract.
   *
   * @param  string  $abstract
   * @return mixed   $creator
   */
  protected function getCreator($abstract)
  {
    $abstract = $this->getAlias($abstract);
    if (!isset($this->bindings[$abstract])) {
      return $abstract;
    } else {
      return $this->bindings[$abstract]['creator'];
    }
  }

  /**
   * Instantiate a creator instance of the given type.
   *
   * @param  string  $creator
   * @param  array   $parameters
   * @return mixed
   */
  public function build($creator, $parameters = array())
  {
    if ($creator instanceof Closure) {
      return $creator($this, $parameters);
    }

    $reflector = new ReflectionClass($creator);

    if (!$reflector->isInstantiable()) {
      $message = "Target [$creator] is not instantiable.";

      throw new BindingException($message);
    }

    $constructor = $reflector->getConstructor();

    if (is_null($constructor)) {
      return new $creator;
    }

    $cparameters = $constructor->getParameters();

    $dependencies = $this->getDependencies($cparameters, $parameters);

    return $reflector->newInstanceArgs($dependencies);
  }

  /**
   * Register a new resolving callback.
   *
   * @param  string  $abstract
   * @param  \Closure  $callback
   * @return void
   */
  public function resolving($abstract, Closure $callback)
  {
    $abstract = $this->getAlias($abstract);
    $this->resolvingCallbacks[$abstract][] = $callback;
  }

  /**
   * Register a new resolving callback for all types.
   *
   * @param  \Closure  $callback
   * @return void
   */
  public function resolvingAny(Closure $callback)
  {
    $this->globalResolvingCallbacks[] = $callback;
  }

  /**
   * Fire all of the resolving callbacks.
   *
   * @param  mixed  $object
   * @return void
   */
  protected function fireResolvingCallbacks($abstract, $object)
  {
    $abstract = $this->getAlias($abstract);

    if (isset($this->resolvingCallbacks[$abstract])) {
      $this->fireCallbackArray($object, $this->resolvingCallbacks[$abstract]);
    }

    $this->fireCallbackArray($object, $this->globalResolvingCallbacks);
  }

  /**
   * Fire an array of callbacks with an object.
   *
   * @param  mixed  $object
   * @param  array  $callbacks
   */
  protected function fireCallbackArray($object, array $callbacks)
  {
    foreach ($callbacks as $callback) {
      call_user_func($callback, $object);
    }
  }

  /**
   * Determine if a given type is shared.
   *
   * @param  string  $abstract
   * @return bool
   */
  protected function isShared($abstract)
  {
    $abstract = $this->getAlias($abstract);
    $set = isset($this->bindings[$abstract]['shared']);

    return $set and $this->bindings[$abstract]['shared'] === true;
  }

  /**
   * Determine if the given creator is buildable.
   *
   * @param  mixed   $creator
   * @param  string  $abstract
   * @return bool
   */
  protected function isBuildable($creator, $abstract)
  {
    return $creator === $abstract || $creator instanceof Closure;
  }

  /**
   * Get the alias for an abstract if available.
   *
   * @param  string  $abstract
   * @return string
   */
  protected function getAlias($abstract)
  {
    return isset($this->aliases[$abstract]) ? $this->getAlias($this->aliases[$abstract]) : $abstract;
  }

  /**
   * Get the container's bindings.
   *
   * @return array
   */
  public function getBindings()
  {
    return $this->bindings;
  }

  /**
   * Determine if a given offset exists.
   *
   * @param  string  $key
   * @return bool
   */
  public function offsetExists($key): bool
  {
    return $this->bound($key);
  }

  /**
   * Get the value at a given offset.
   *
   * @param  string  $key
   * @return mixed
   */
  public function offsetGet($key): mixed
  {
    return $this->make($key);
  }

  /**
   * Set the value at a given offset.
   *
   * @param  string  $key
   * @param  mixed   $value
   */
  public function offsetSet($key, $value): void
  {
    if (!$value instanceof Closure) {
      $value = function () use ($value) {
        return $value;
      };
    }

    $this->bind($key, $value);
  }

  /**
   * Unset the value at a given offset.
   *
   * @param  string  $key
   */
  public function offsetUnset($key): void
  {
    unset($this->bindings[$key], $this->instances[$key]);
  }

  public function withProvider($class)
  {
    $this->providers[] = $class;
    return $this;
  }

  public function withProviders($classes)
  {
    if (is_string($classes))
      $classes = func_get_args();

    $this->providers = [...$this->providers, ...$classes];

    return $this;
  }

  public function boot()
  {
    $this->booted = false;
    $instances = [];


    foreach ($this->providers as $provider) {
      $instances[] = $this->make($provider);
    }

    foreach ($instances as $instance) {
      $this->call([$instance, 'register']);
    }

    foreach ($instances as $instance) {
      $this->call([$instance, 'boot']);
    }

    $this->booted = true;

    return $this;
  }
}