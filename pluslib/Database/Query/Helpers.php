<?php
namespace pluslib\Database\Query;

defined('ABSPATH') || exit;

use pluslib\Database\Expression;

final class Helpers
{
  private function __construct()
  {
  }
  public static function NormalizeColumnName($rawName)
  {
    if ($rawName instanceof Expression) {
      return $rawName->raw;
    } else if (!str_starts_with($rawName, '`') && is_string($rawName)) {
      $rawName = "`" . addslashes($rawName) . "`";
    }

    return $rawName;
  }
  public static function NormalizeTableName($rawName, $rawAlias = null)
  {
    $name = static::NormalizeColumnName($rawName);
    $alias = static::NormalizeColumnName($rawAlias) ?? ''; // NCN(null) = null

    $as = $alias ? ' as ' : '';

    return $name . $as . $alias;
  }
  public static function NormalizeValue($raw)
  {
    if ($raw instanceof Expression) {
      return $raw->raw;
    } elseif ($raw === null) {
      return 'NULL';
    } elseif (is_bool($raw)) {
      return $raw ? '1' : '0';
    } elseif (is_string($raw) && $raw !== '?') {
      return "'" . addslashes($raw) . "'";
    }

    return $raw;
  }

  public static function NormalizeArray($array)
  {
    return array_combine(
      array_map(fn($k) => static::NormalizeColumnName($k), array_keys($array)),
      array_map(fn($v) => static::NormalizeValue($v), array_values($array)),
    );
  }
}