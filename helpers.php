<?php

defined('ABSPATH') || exit;

function truncate($string, $chars = 50, $terminator = ' …')
{
  if (mb_strlen($string) < $chars) {
    return $string;
  }
  $cutPos = $chars - mb_strlen($terminator);
  $boundaryPos = mb_strrpos(mb_substr($string, 0, mb_strpos($string, ' ', $cutPos)), ' ');
  return mb_substr($string, 0, $boundaryPos === false ? $cutPos : $boundaryPos) . $terminator;
}

function valueof($fv, ...$data)
{
  $_fv = $fv;
  if (is_callable($fv)) {
    $_fv = call_user_func($fv, ...$data);
  }
  return $_fv;
}

function importJSON($file, $assoc = null, $depth = 512, $flags = 0)
{
  return json_decode(file_get_contents($file), $assoc, $depth, $flags);
}


function number_format_short($n, $precision = 1)
{
  if ($n < 900) {
    // 0 - 900
    $n_format = number_format($n, $precision);
    $suffix = '';
  } else if ($n < 900000) {
    // 0.9k-850k
    $n_format = number_format($n / 1000, $precision);
    $suffix = 'K';
  } else if ($n < 900000000) {
    // 0.9m-850m
    $n_format = number_format($n / 1000000, $precision);
    $suffix = 'M';
  } else if ($n < 900000000000) {
    // 0.9b-850b
    $n_format = number_format($n / 1000000000, $precision);
    $suffix = 'B';
  } else {
    // 0.9t+
    $n_format = number_format($n / 1000000000000, $precision);
    $suffix = 'T';
  }

  // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
  // Intentionally does not affect partials, eg "1.50" -> "1.50"
  if ($precision > 0) {
    $dotzero = '.' . str_repeat('0', $precision);
    $n_format = str_replace($dotzero, '', $n_format);
  }

  return $n_format . $suffix;
}

if (!function_exists('dump')) {
  function dump($obj)
  {
    echo "<pre>";
    var_dump($obj);
    echo "</pre>";
  }
}


if (!function_exists('dd')) {
  function dd($obj)
  {
    dump($obj);
    die;
  }
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
  function data_get($array, $key, $default = null)
  {
    if (!array_accessible($array)) {
      return valueof($default);
    }

    if (is_null($key)) {
      return $array;
    }

    if (array_exists($array, $key)) {
      return $array[$key];
    }

    if (!str_contains($key, '.')) {
      return $array[$key] ?? valueof($default);
    }

    foreach (explode('.', $key) as $segment) {
      if (array_accessible($array) && array_exists($array, $segment)) {
        $array = $array[$segment];
      } else {
        return valueof($default);
      }
    }
    return $array;
  }
}
