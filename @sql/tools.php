<?php
namespace pluslib\Tools;

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

  public static function NormalizeArray($array)
  {
    return array_combine(
      array_map(function ($v) {
        return static::NormalizeColumnName($v);
      }, array_keys($array)),
      array_values($array),
    );
  }
}