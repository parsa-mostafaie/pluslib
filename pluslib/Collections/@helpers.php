<?php
include_once '@array.php';

use pluslib\Collections\Collection;

function collect(array|Collection $array = []): Collection
{
  if ($array instanceof Collection) {
    return $array;
  }
  return new Collection($array);
}

function uncollect(array|Collection $array)
{
  if (is_array($array)) {
    return $array;
  }
  return $array->all();
}