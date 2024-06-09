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