<?php
namespace pluslib\helpers;

defined('ABSPATH') || exit;

final class QueryBuilding
{
  private function __construct()
  {
  }
  public static function NormalizeColumnName($rawName)
  {
    if (!str_starts_with($rawName, '`')) {
      $rawName = "`" . addslashes($rawName) . "`";
    }
    return $rawName;
  }
  public static function NormalizeValue($raw)
  {
    if (is_string($raw) && $raw !== '?' && !($raw instanceof \SExpression)) {
      return "'" . addslashes($raw) . "'";
    } elseif ($raw === null) {
      return 'NULL';
    } elseif ($raw instanceof \SExpression) {
      return $raw->raw;
    }
    return $raw;
  }

  public static function NormalizeArray($array)
  {
    return array_combine(
      array_map(function ($v) {
        return static::NormalizeColumnName($v);
      }, array_keys($array)),
      array_map(fn($v) => static::NormalizeValue($v), array_values($array)),
    );
  }
}