<?php
namespace pluslib\Database;


abstract class UploadBaseColumn
{
  protected ?string $table;

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
    $temp = static::tbl()->UPDATE(static::cond($id))->Set([$_->colName => "?"])->Run([$v]);
    return $temp;
  }
  public static function get_url($id)
  {
    return upload_url(static::val($id));
  }

  static function rem($id)
  {
    if (static::has($id)) {
      unlinkUpload(static::val($id));
      return static::set($id, 'NULL');
    }
  }

  static function has($id)
  {
    $_purl = static::get_url($id);
    $purl = upload_path($_purl);
    return file_exists($purl) && static::get_url($id);
  }

  static function val($id)
  {
    $_ = new static;

    return static::tbl()->SELECT([$_->colName])->WHERE(static::cond($id))->Run()->fetchColumn();
  }

  protected static function tbl()
  {
    return db()->TABLE((new static)->table);
  }
}
