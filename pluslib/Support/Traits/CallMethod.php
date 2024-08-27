<?php
namespace pluslib\Support\Traits;

use InvalidArgumentException;
use ReflectionFunction;
use ReflectionMethod;

trait CallMethod
{
  /**
   * Determine if the given string is in Class@method syntax.
   *
   * @param  mixed  $callback
   * @return bool
   */
  protected function isCallableWithAtSign($callback)
  {
    return is_string($callback) && strpos($callback, '@') !== false;
  }


  /**
   * Get the proper reflection instance for the given callback.
   *
   * @param  callable|string  $callback
   * @return \ReflectionFunctionAbstract
   */
  protected function getCallReflector($callback)
  {
    if (is_string($callback) && strpos($callback, '::') !== false) {
      $callback = explode('::', $callback);
    }

    if (is_array($callback)) {
      return new ReflectionMethod($callback[0], $callback[1]);
    }

    return new ReflectionFunction($callback);
  }

  /**
   * Converts atsign syntax callback to php callable
   * 
   * @return callable
   */
  protected function toCallable($callback, $defaultMethod = '__invoke')
  {
    if (
      $this->isCallableWithAtSign($callback)
      ||
      (is_string($callback) && !is_callable($callback))
    ) {
      $segments = explode('@', $callback);

      $method = count($segments) == 2 ? $segments[1] : $defaultMethod;

      if (is_null($method)) {
        throw new InvalidArgumentException('Method not provided.');
      }

      return [$this->make($segments[0]), $method];
    }

    return $callback;
  }

  function make($class)
  {
    return new $class;
  }
}