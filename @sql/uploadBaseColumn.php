<?php
namespace pluslib\SQL;

use Sql_Table;

defined('ABSPATH') || exit;

class UploadBaseColumn
{
  protected ?Sql_Table $tbl;
  protected string $colName; // Colname
  protected int $maxSize = 3145728;
  protected array $allowedTypes = ['image/png' => 'png', 'image/jpeg' => 'jpg'];
  protected string $prefix = '';
  protected mixed $primaryKey = 'ID';
  protected string $customAttrs = '';
  protected string $altImage = '/default_uploads/unknown.png';
  protected bool $srcAttr = true;
  public function __construct(
  ) {
  }
  public static function cond($id)
  {
    return (new static)->primaryKey . ' = ' . $id;
  }
  public static function setFromInput(
    $id,
    $name
  ) {
    $_ = new static;
    $file = uploadFile_secure($name, $_->maxSize, $_->allowedTypes, $_->prefix);
    if ($file) {
      static::rem($id);
      return static::set($id, $file);
    }
    return $file;
  }
  private static function set(
    $id,
    $v
  ) {
    $_ = new static;
    $temp = $_->tbl->UPDATE(static::cond($id))->Set($_->colName . " = ?")->Run([$v]);
    return $temp;
  }
  public static function get_url($id)
  {
    return urlOfUpload(static::val($id));
  }

  static function rem($id)
  {
    if (static::has($id)) {
      unlinkUpload(static::val($id));
      return static::set($id, 'NULL');
    }
  }

  public static function get_img(
    $id,
    $cattrs = '',
    $echo = false,
  ) {
    $_ = new static;
    $purl =
      static::get_url($id);
    return imageComponent($purl, $_->customAttrs . $cattrs, $_->altImage, $echo, $_->srcAttr);
  }

  static function has($id)
  {
    $_purl = static::get_url($id);
    $purl = $_SERVER['DOCUMENT_ROOT'] . regular_url($_purl);
    return file_exists($purl) && static::get_url($id);
  }

  static function val($id)
  {
    $_ = new static;

    return $_->tbl->SELECT([$_->colName])->WHERE(static::cond($id))->Run()->fetchColumn();
  }
}

//? Example:
// class UserProfile extends UploadBaseColumn
// {

//   function __construct()
//   {
//     parent::__construct();

//     $this->tbl = db()->TABLE('users');
//     $this->prefix = 'PROFILE_';
//     $this->colName = 'profile';
//   }
// }

include_once 'ubc.defaults.php';