<?php
defined('ABSPATH') || exit;

use pluslib\Collections\Arr;
use pluslib\Collections\Collection;

//NOTE: THIS IS ALWAYS PUBLIC
function array_any(array $array, callable $fn)
{
  foreach ($array as $value) {
    if ($fn($value)) {
      return true;
    }
  }
  return false;
}

function array_every(array $array, callable $fn)
{
  foreach ($array as $value) {
    if (!$fn($value)) {
      return false;
    }
  }
  return true;
}

/**
 * Recursively trim strings in an array
 * @param array $items
 * @return array
 */
function array_trim(array $items): array
{
  return array_map(function ($item) {
    if (is_string($item)) {
      return trim($item);
    } elseif (is_array($item)) {
      return array_trim($item);
    } else
      return $item;
  }, $items);
}

function array_dot(array $item, $context = '')
{
  $retval = [];
  foreach ($item as $key => $value) {
    if (is_array($value) === true) {
      foreach (array_dot($value, "$context$key.") as $iKey => $iValue) {
        $retval[$iKey] = $iValue;
      }
    } else {
      $retval["$context$key"] = $value;
    }
  }
  return $retval;
}


if (!function_exists('array_accessible')) {
  function array_accessible($array)
  {
    return Arr::accessible($array);
  }
}

if (!function_exists('array_exists')) {
  function array_exists($array, $key)
  {
    return Arr::exists($array, $key);
  }
}

function to_array($arrayable)
{
  if (is_array($arrayable)) {
    return $arrayable;
  } elseif ($arrayable instanceof Collection)
    return $arrayable->all();
  elseif ($arrayable instanceof stdClass) {
    return (array) $arrayable;
  }
  return [];
}

function wrap($arr)
{
  return Arr::wrap($arr);
}

if (!function_exists('head')) {
  /**
   * Gets First element of array
   * @param $arr
   * @return mixed
   */
  function head($arr)
  {
    $arr = to_array($arr);
    return reset($arr);
  }
}

if (!function_exists('last')) {
  /**
   * Gets Last element of array
   * @param $arr
   * @return mixed
   */
  function last($arr)
  {
    $arr = to_array($arr);
    return end($arr);
  }
}

if (!function_exists('data_fill')) {
  /**
   * Fill in data where it's missing.
   *
   * @param  mixed  $target
   * @param  string|array  $key
   * @param  mixed  $value
   * @return mixed
   */
  function data_fill(&$target, $key, $value)
  {
    return data_set($target, $key, $value, false);
  }
}

if (!function_exists('data_get')) {
  /**
   * Get an item from an array or object using "dot" notation.
   *
   * @param  mixed  $target
   * @param  string|array|int|null  $key
   * @param  mixed  $default
   * @return mixed
   */
  function data_get($target, $key, $default = null)
  {
    if (is_null($key)) {
      return $target;
    }

    $key = is_array($key) ? $key : explode('.', $key);

    foreach ($key as $i => $segment) {
      unset($key[$i]);

      if (is_null($segment)) {
        return $target;
      }

      if ($segment === '*') {
        if ($target instanceof Collection) {
          $target = $target->all();
        } elseif (!is_iterable($target)) {
          return value($default);
        }

        $result = [];

        foreach ($target as $item) {
          $result[] = data_get($item, $key);
        }

        return in_array('*', $key) ? Arr::collapse($result) : $result;
      }
      $segment = match ($segment) {
        '\*' => '*',
        '\{first}' => '{first}',
        '{first}' => array_key_first(collect($target)->all()),
        '\{last}' => '{last}',
        '{last}' => array_key_last(collect($target)->all()),
        default => $segment,
      };

      if (Arr::accessible($target) && Arr::exists($target, $segment)) {
        $target = $target[$segment];
      } elseif (is_object($target) && isset($target->{$segment})) {
        $target = $target->{$segment};
      } else {
        return value($default);
      }
    }

    return $target;
  }
}

if (!function_exists('data_set')) {
  /**
   * Set an item on an array or object using dot notation.
   *
   * @param  mixed  $target
   * @param  string|array  $key
   * @param  mixed  $value
   * @param  bool  $overwrite
   * @return mixed
   */
  function data_set(&$target, $key, $value, $overwrite = true)
  {
    $segments = is_array($key) ? $key : explode('.', $key);

    if (($segment = array_shift($segments)) === '*') {
      if (!Arr::accessible($target)) {
        $target = [];
      }

      if ($segments) {
        foreach ($target as &$inner) {
          data_set($inner, $segments, $value, $overwrite);
        }
      } elseif ($overwrite) {
        foreach ($target as &$inner) {
          $inner = $value;
        }
      }
    } elseif (Arr::accessible($target)) {
      if ($segments) {
        if (!Arr::exists($target, $segment)) {
          $target[$segment] = [];
        }

        data_set($target[$segment], $segments, $value, $overwrite);
      } elseif ($overwrite || !Arr::exists($target, $segment)) {
        $target[$segment] = $value;
      }
    } elseif (is_object($target)) {
      if ($segments) {
        if (!isset($target->{$segment})) {
          $target->{$segment} = [];
        }

        data_set($target->{$segment}, $segments, $value, $overwrite);
      } elseif ($overwrite || !isset($target->{$segment})) {
        $target->{$segment} = $value;
      }
    } else {
      $target = [];

      if ($segments) {
        data_set($target[$segment], $segments, $value, $overwrite);
      } elseif ($overwrite) {
        $target[$segment] = $value;
      }
    }

    return $target;
  }
}

if (!function_exists('data_forget')) {
  /**
   * Remove / unset an item from an array or object using "dot" notation.
   *
   * @param  mixed  $target
   * @param  string|array|int|null  $key
   * @return mixed
   */
  function data_forget(&$target, $key)
  {
    $segments = is_array($key) ? $key : explode('.', $key);

    if (($segment = array_shift($segments)) === '*' && Arr::accessible($target)) {
      if ($segments) {
        foreach ($target as &$inner) {
          data_forget($inner, $segments);
        }
      }
    } elseif (Arr::accessible($target)) {
      if ($segments && Arr::exists($target, $segment)) {
        data_forget($target[$segment], $segments);
      } else {
        Arr::forget($target, $segment);
      }
    } elseif (is_object($target)) {
      if ($segments && isset($target->{$segment})) {
        data_forget($target->{$segment}, $segments);
      } elseif (isset($target->{$segment})) {
        unset($target->{$segment});
      }
    }

    return $target;
  }
}

if (!function_exists('join_assoc')) {
  function join_assoc($arr, $key_val = " = ", $pair_sep = ", ")
  {
    return join($pair_sep, array_map(fn($k, $v) => $k . $key_val . $v, array_keys($arr), $arr));
  }
}
