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

if (!function_exists('optional')) {
  /**
   * IF $obj != null: Returns the $obj (Or $closure($obj) IF $closure be instanceof Closure), 
   * else returns a object that returns null for all properties or methods!
   * @param mixed $obj
   * @param mixed $closure
   * @return mixed
   */
  function optional($obj, $closure = null)
  {
    if (is_null($obj)) {
      return new class {
        function __get($prop)
        {
          return null;
        }
        function __call($f, $p)
        {
          return null;
        }
      };
    }
    if ($closure instanceof Closure && !is_null($closure)) {
      return $closure($obj);
    }
    return $obj;
  }
}

include_once '@info.php';
include_once '@url.php';
include_once '@path.php';
include_once '@session.php';
include_once 'HTTP/@helpers.php';
include_once 'Collections/@helpers.php';
include_once '@Admin/@helpers.php';
include_once '@User/@helpers.php';
include_once 'Security/@helpers.php';
include_once '@Form/@helpers.php';
include_once 'Database/@helpers.php';