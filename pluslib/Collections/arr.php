<?php
namespace pluslib\Collections;

defined('ABSPATH') || exit;

use pluslib\Collections\Collection;
use ArrayAccess;

class Arr
{
  /**
   * Determine whether the given value is array accessible.
   *
   * @param  mixed  $value
   * @return bool
   */
  public static function accessible($value)
  {
    return is_array($value) || $value instanceof ArrayAccess;
  }

  /**
   * Determine if the given key exists in the provided array.
   *
   * @param  \ArrayAccess|array  $array
   * @param  string|int  $key
   * @return bool
   */
  public static function exists($array, $key)
  {
    if ($array instanceof ArrayAccess) {
      return $array->offsetExists($key);
    }

    if (is_float($key)) {
      $key = (string) $key;
    }

    return array_key_exists($key, $array);
  }

  /**
   * Get an item from an array using "dot" notation.
   *
   * @param  \ArrayAccess|array  $array
   * @param  string|int|null  $key
   * @param  mixed  $default
   * @return mixed
   */
  public static function get($array, $key, $default = null)
  {
    if (!static::accessible($array)) {
      return value($default);
    }

    if (is_null($key)) {
      return $array;
    }

    if (static::exists($array, $key)) {
      return $array[$key];
    }

    if (!str_contains($key, '.')) {
      return $array[$key] ?? value($default);
    }

    foreach (explode('.', $key) as $segment) {
      if (static::accessible($array) && static::exists($array, $segment)) {
        $array = $array[$segment];
      } else {
        return value($default);
      }
    }

    return $array;
  }

  /**
   * Set an array item to a given value using "dot" notation.
   *
   * If no key is given to the method, the entire array will be replaced.
   *
   * @param  array  $array
   * @param  string|int|null  $key
   * @param  mixed  $value
   * @return array
   */
  public static function set(&$array, $key, $value)
  {
    if (is_null($key)) {
      return $array = $value;
    }

    $keys = explode('.', $key);

    foreach ($keys as $i => $key) {
      if (count($keys) === 1) {
        break;
      }

      unset($keys[$i]);

      // If the key doesn't exist at this depth, we will just create an empty array
      // to hold the next value, allowing us to create the arrays to hold final
      // values at the correct depth. Then we'll keep digging into the array.
      if (!isset($array[$key]) || !is_array($array[$key])) {
        $array[$key] = [];
      }

      $array = &$array[$key];
    }

    $array[array_shift($keys)] = $value;

    return $array;
  }

  /**
   * Add an element to an array using "dot" notation if it doesn't exist.
   *
   * @param  array  $array
   * @param  string|int|float  $key
   * @param  mixed  $value
   * @return array
   */
  public static function add($array, $key, $value)
  {
    if (is_null(static::get($array, $key))) {
      static::set($array, $key, $value);
    }

    return $array;
  }

  /**
   * Collapse an array of arrays into a single array.
   *
   * @param  iterable  $array
   * @return array
   */
  public static function collapse($array)
  {
    $results = [];

    foreach ($array as $values) {
      if ($values instanceof Collection) {
        $values = $values->all();
      } elseif (!is_array($values)) {
        continue;
      }

      $results[] = $values;
    }

    return array_merge([], ...$results);
  }

  /**
   * Cross join the given arrays, returning all possible permutations.
   *
   * @param  iterable  ...$arrays
   * @return array
   */
  public static function crossJoin(...$arrays)
  {
    $results = [[]];

    foreach ($arrays as $index => $array) {
      $append = [];

      foreach ($results as $product) {
        foreach ($array as $item) {
          $product[$index] = $item;

          $append[] = $product;
        }
      }

      $results = $append;
    }

    return $results;
  }

  /**
   * Divide an array into two arrays. One with keys and the other with values.
   *
   * @param  array  $array
   * @return array
   */
  public static function divide($array)
  {
    return [array_keys($array), array_values($array)];
  }

  /**
   * Flatten a multi-dimensional associative array with dots.
   *
   * @param  iterable  $array
   * @param  string  $prepend
   * @return array
   */
  public static function dot($array, $prepend = '')
  {
    $results = [];

    foreach ($array as $key => $value) {
      if (is_array($value) && !empty($value)) {
        $results = array_merge($results, static::dot($value, $prepend . $key . '.'));
      } else {
        $results[$prepend . $key] = $value;
      }
    }

    return $results;
  }

  /**
   * Convert a flatten "dot" notation array into an expanded array.
   *
   * @param  iterable  $array
   * @return array
   */
  public static function undot($array)
  {
    $results = [];

    foreach ($array as $key => $value) {
      static::set($results, $key, $value);
    }

    return $results;
  }

  /**
   * Get all of the given array except for a specified array of keys.
   *
   * @param  array  $array
   * @param  array|string|int|float  $keys
   * @return array
   */
  public static function except($array, $keys)
  {
    static::forget($array, $keys);

    return $array;
  }

  /**
   * Remove one or many array items from a given array using "dot" notation.
   *
   * @param  array  $array
   * @param  array|string|int|float  $keys
   * @return void
   */
  public static function forget(&$array, $keys)
  {
    $original = &$array;

    $keys = (array) $keys;

    if (count($keys) === 0) {
      return;
    }

    foreach ($keys as $key) {
      // if the exact key exists in the top-level, remove it
      if (static::exists($array, $key)) {
        unset($array[$key]);

        continue;
      }

      $parts = explode('.', $key);

      // clean up before each pass
      $array = &$original;

      while (count($parts) > 1) {
        $part = array_shift($parts);

        if (isset($array[$part]) && static::accessible($array[$part])) {
          $array = &$array[$part];
        } else {
          continue 2;
        }
      }

      unset($array[array_shift($parts)]);
    }
  }

  /**
   * Check if an item or items exist in an array using "dot" notation.
   *
   * @param  \ArrayAccess|array  $array
   * @param  string|array  $keys
   * @return bool
   */
  public static function has($array, $keys)
  {
    $keys = (array) $keys;

    if (!$array || $keys === []) {
      return false;
    }

    foreach ($keys as $key) {
      $subKeyArray = $array;

      if (static::exists($array, $key)) {
        continue;
      }

      foreach (explode('.', $key) as $segment) {
        if (static::accessible($subKeyArray) && static::exists($subKeyArray, $segment)) {
          $subKeyArray = $subKeyArray[$segment];
        } else {
          return false;
        }
      }
    }

    return true;
  }

  /**
   * Determine if any of the keys exist in an array using "dot" notation.
   *
   * @param  \ArrayAccess|array  $array
   * @param  string|array  $keys
   * @return bool
   */
  public static function hasAny($array, $keys)
  {
    if (is_null($keys)) {
      return false;
    }

    $keys = (array) $keys;

    if (!$array) {
      return false;
    }

    if ($keys === []) {
      return false;
    }

    foreach ($keys as $key) {
      if (static::has($array, $key)) {
        return true;
      }
    }

    return false;
  }

  /**
   * Determines if an array is associative.
   *
   * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
   *
   * @param  array  $array
   * @return bool
   */
  public static function isAssoc(array $array)
  {
    return !array_is_list($array);
  }

  /**
   * Determines if an array is a list.
   *
   * An array is a "list" if all array keys are sequential integers starting from 0 with no gaps in between.
   *
   * @param  array  $array
   * @return bool
   */
  public static function isList($array)
  {
    return array_is_list($array);
  }

  /**
   * Join all items using a string. The final items can use a separate glue string.
   *
   * @param  array  $array
   * @param  string  $glue
   * @param  string  $finalGlue
   * @return string
   */
  public static function join($array, $glue, $finalGlue = '')
  {
    if ($finalGlue === '') {
      return implode($glue, $array);
    }

    if (count($array) === 0) {
      return '';
    }

    if (count($array) === 1) {
      return end($array);
    }

    $finalItem = array_pop($array);

    return implode($glue, $array) . $finalGlue . $finalItem;
  }

  /**
   * Get a subset of the items from the given array.
   *
   * @param  array  $array
   * @param  array|string  $keys
   * @return array
   */
  public static function only($array, $keys)
  {
    return array_intersect_key($array, array_flip((array) $keys));
  }

  /**
   * Select an array of values from an array.
   *
   * @param  array  $array
   * @param  array|string  $keys
   * @return array
   */
  public static function select($array, $keys)
  {
    $keys = static::wrap($keys);

    return static::map($array, function ($item) use ($keys) {
      $result = [];

      foreach ($keys as $key) {
        if (Arr::accessible($item) && Arr::exists($item, $key)) {
          $result[$key] = $item[$key];
        } elseif (is_object($item) && isset($item->{$key})) {
          $result[$key] = $item->{$key};
        }
      }

      return $result;
    });
  }

  /**
   * If the given value is not an array and not null, wrap it in one.
   *
   * @param  mixed  $value
   * @return array
   */
  public static function wrap($value)
  {
    if (is_null($value)) {
      return [];
    }

    return is_array($value) ? $value : [$value];
  }

  /**
   * Run a map over each of the items in the array.
   *
   * @param  array  $array
   * @param  callable  $callback
   * @return array
   */
  public static function map(array $array, callable $callback)
  {
    $keys = array_keys($array);

    try {
      $items = array_map($callback, $array, $keys);
    } catch (\ArgumentCountError) {
      $items = array_map($callback, $array);
    }

    return array_combine($keys, $items);
  }
}