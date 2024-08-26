<?php
namespace pluslib\Foundation;

use ArrayAccess;
use Closure;
use Exception;
use pluslib\Database\DB;
use pluslib\HTTP\Response;
use pluslib\HTTP\RestAPI;
use pluslib\Support\Application;
use pluslib\Router\Router as Route;

class Container implements ArrayAccess
{
  protected $bindings = [];

  public function __construct()
  {
    $this->bindings = $this->defaultBindings();
  }

  public function defaultBindings()
  {
    return [
      'application' => fn() => $this,
      'database' => DB::class,
      'rest' => RestAPI::class,
      'route' => Route::class
    ];
  }

  public function toClosure($creator)
  {
    return function ($parameters) use ($creator) {
      if (!$creator instanceof Closure) {
        if (is_string($creator)) {
          if (!class_exists($creator)) {
            throw new \InvalidArgumentException("Creator $creator should be an Existing Class");
          }

          return new $creator(...$parameters);
        } else {
          throw new \InvalidArgumentException("Creator should be Closure|string|null");
        }
      }

      return $creator(...$parameters);
    };
  }

  public function bind($accessor, $creator = null, $singleton = false)
  {
    if (is_null($creator)) {
      $creator = $accessor;
    }

    $this->bindings[$accessor] = ['creator' => $this->toClosure($creator), 'shared' => $singleton];

    return $this->bindings[$accessor];
  }

  public function singleton($accessor, $creator = null)
  {
    return $this->bind($accessor, $creator, true);
  }

  protected function &getBinding($accessor)
  {
    $binding = &$this->bindings[$accessor];

    if (!is_array($binding)) {
      $binding = $this->bind($accessor, $binding, true);
    }

    return $binding;
  }

  public function make($accessor, $parameters = [])
  {
    $binding = &$this->getBinding($accessor);

    if ($binding['shared']) {
      if (!isset($binding['instance'])) {
        $binding['instance'] = $binding['creator']($parameters);
      }

      return $binding['instance'];
    }

    return $binding['creator']($parameters);
  }

  public function has($binding)
  {
    return isset($this->bindings[$binding]);
  }

  public function offsetGet($offset): mixed
  {
    return $this->make($offset);
  }

  public function offsetExists($offset): bool
  {
    return $this->has($offset);
  }

  public function offsetSet($offset, $value): never
  {
    throw new Exception(static::class . ' does not implements offsetSet');
  }

  public function offsetUnSet($offset): void
  {
    throw new Exception('Can\'t unset a container binding! ' . $offset);
  }
}