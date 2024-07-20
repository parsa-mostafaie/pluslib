<?php
namespace pluslib\Database\Deprecated;

defined('ABSPATH') || exit;

use PDOStatement, PDO;

/**
 * @deprecated
 */
class sqlRow
{
  public array $row;
  public $found = false;
  public function __construct(
    public readonly PDOStatement $stmt
  ) {
    $t = $this->stmt->fetch(PDO::FETCH_ASSOC);
    $this->row = $t ? $t : [];
    if ($t) {
      $this->found = true;
    }
  }
  public function getColumn($cn)
  {
    return $this->row[$cn];
  }
  public function getAssetBasedCol(
    $cn,
    $maxSize = 3145728,
    $allowedTypes = [
      'image/png' => 'png',
      'image/jpeg' => 'jpg'
    ],
    $prefix = ''
  ) {
    return new sql_abcol($cn, $this->row[$cn], $maxSize, $allowedTypes, $prefix);
  }
  public function __get($name)
  {
    return $this->getColumn($name);
  }
}