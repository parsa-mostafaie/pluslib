<?php
namespace pluslib\Foundation;

use InvalidArgumentException;
use pluslib\Support\Traits\CallMethod;
use ReflectionParameter;

trait CallResolver
{
  use CallMethod;

  /**
   * Call the given Closure / class@method and inject its dependencies.
   *
   * @param  callable|string  $callback
   * @param  array  $parameters
   * @param  string|null  $defaultMethod
   * @return mixed
   */
  public function call($callback, array $parameters = [], $defaultMethod = '__invoke')
  {
    $callback = $this->toCallable($callback, $defaultMethod);

    $dependencies = $this->getMethodDependencies($callback, $parameters);

    return $callback(...$dependencies);
  }

  /**
   * Get all dependencies for a given method.
   *
   * @param  callable|string  $callback
   * @param  array  $parameters
   * @return array
   */
  protected function getMethodDependencies($callback, array $parameters = [])
  {
    return $this->getDependencies($this->getCallReflector($callback)->getParameters(), $parameters);
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

      if (isset($alternatives[$parameter->name])) {
        $dependencies[] = $alternatives[$parameter->name];
      } elseif (!$dependency || $dependency->isBuiltin()) {
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