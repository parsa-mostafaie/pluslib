<?php
namespace pluslib\Foundation;

use Closure;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

trait CallResolver
{
  public function call($method, $alternatives = [])
  {
    $reflection = null;

    if (is_string($method) && !is_callable(value: $method)) {
      $method = [$method, '__invoke'];
    } else if ($method instanceof Closure) {
      $reflection = new ReflectionFunction($method);
    }

    if (!is_callable($method)) {
      throw new InvalidArgumentException("Argument `method` of " . static::class . "::call should be callable");
    }

    if (!$reflection) {
      $reflection = new ReflectionMethod($method);
    }

    $parameters = $reflection->getParameters();

    return $method($this->getDependencies($parameters, $alternatives));
  }

  /**
   * Resolve all of the dependencies from the ReflectionParameters.
   *
   * @param  array  $parameters
   * @return array
   */
  protected function getDependencies($parameters, $alternatives = [])
  {
    $dependencies = array();

    /**
     * @var ReflectionParameter $parameter
     */
    foreach ($parameters as $parameter) {
      $dependency = $parameter->getType();

      if(isset($alternatives[$parameter->name])){
        $dependencies[] = $alternatives[$parameter->name];
      }
      elseif (!$dependency || $dependency->isBuiltin()) {
        $dependencies[] = $this->resolveNonClass($parameter);
      } else {
        $dependencies[] = $this->resolveClass($parameter);
      }
    }

    return (array) $dependencies;
  }

  /**
   * Resolve a non-class hinted dependency.
   *
   * @param  ReflectionParameter  $parameter
   * @return mixed
   */
  protected function resolveNonClass(ReflectionParameter $parameter)
  {
    if ($parameter->isDefaultValueAvailable()) {
      return $parameter->getDefaultValue();
    }
    $message = "Unresolvable dependency resolving [$parameter].";

    throw new BindingException($message);
  }

  /**
   * Resolve a class based dependency from the container.
   *
   * @param  \ReflectionParameter  $parameter
   * @return mixed
   */
  protected function resolveClass(ReflectionParameter $parameter)
  {
    try {
      echo "notClass {$parameter->name}";
      return $this->make($parameter->getType()->getName());
    } catch (BindingException $e) {
      if ($parameter->isOptional()) {
        return $parameter->getDefaultValue();
      } else {
        throw $e;
      }
    }
  }

}