<?php
defined('ABSPATH') || exit;

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
    return (is_array($array) || $array instanceof ArrayAccess);
  }
}

if (!function_exists('array_exists')) {
  function array_exists($array, $key)
  {
    if ($array instanceof ArrayAccess) {
      return $array->offsetExists($key);
    }

    if (is_float($key)) {
      $key = (string) $key;
    }

    return array_key_exists($key, $array);
  }
}

if (!function_exists('data_get')) {
  function data_get($data, $key, $default = null)
  {
    $default = valueof($default);

    $data = to_array($data);

    if (!array_accessible($data)) {
      return $default;
    }

    if (is_null($key)) {
      return $data;
    }

    if (array_exists($data, $key)) {
      return $data[$key];
    }

    if (!str_contains($key, '.')) {
      return $data[$key] ?? $default;
    }

    $segments = explode('.', $key);

    foreach ($segments as $index => $segment) {
      $data = to_array($data);
      if ($segment === '*') {
        $results = [];
        foreach ($data as $value) {
          $key = implode('.', array_slice($segments, $index + 1));
          $results[] = data_get($value, $key);
        }
        return $results;
      }

      if (array_accessible($data)) {
        if ($segment === '{first}') {
          $data = reset($data);
        } elseif ($segment === '{last}') {
          $data = end($data);
        } elseif (array_exists($data, $segment)) {
          $data = $data[$segment];
        }
      } else {
        return $default;
      }
    }

    return $data;
  }
}

function to_array($arrayable)
{
  if (is_array($arrayable)) {
    return $arrayable;
  } elseif ($arrayable instanceof pluslib\Collections\Collection)
    return $arrayable->all();
  elseif ($arrayable instanceof stdClass) {
    return (array) $arrayable;
  }
  return [];
}

function wrap($arr)
{
  if (array_accessible($arr)) {
    return to_array($arr);
  }
  return [$arr];
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
    return first($arr);
  }
}

if (!function_exists('last')) {
  /**
   * Gets Last element of array
   * @param $arr
   * @return mixed
   */
  function head($arr)
  {
    $arr = to_array($arr);
    return end($arr);
  }
}
