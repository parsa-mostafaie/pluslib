<?php
//NOTE: THIS NEED CUSTOMIZATION IN LINE THAT MARKED BY *

date_default_timezone_set('Asia/Tehran');

use pluslib\Database\Expression;

use pluslib\Support\Facades\DB as dbFacade;

function db()
{
  return dbFacade::singleton();
}

if (!function_exists('expr')) {
  function expr($raw)
  {
    return new Expression($raw);
  }
}

use pluslib\Database\Query\Helpers as QB;

if (!function_exists('escape')) {
  function escape($raw)
  {
    return QB::NormalizeValue($raw);
  }
}

if (!function_exists('escape_arr')) {
  function escape_arr($raw)
  {
    return QB::NormalizeArray($raw);
  }
}

if (!function_exists('escape_col')) {
  function escape_col($raw)
  {
    return QB::NormalizeColumnName($raw);
  }
}

if (!function_exists('escape_tbl')) {
  function escape_tbl($raw, $alias = null)
  {
    return QB::NormalizeTableName($raw, $alias);
  }
}


if (!function_exists('cond')) {
  function cond($cond = null, $operator = null, $value = null)
  {
    return new pluslib\Database\Condition(...func_get_args());
  }
}