<?php
namespace pluslib\config\SQL;

use Sql_Table;

include_once '../init.php';

class abcol_config
{
  public function __construct(
    public readonly Sql_Table $tbl,
    public readonly string $name, // Colname
    public readonly int $ms = 3145728,
    public readonly array $at = ['image/png' => 'png', 'image/jpeg' => 'jpg'],
    public readonly string $pf = '',
    public readonly mixed $pk = null,
    public readonly string $cattrs = '',
    public readonly string $undefined = '/default_uploads/unknown.png',
    public readonly bool $ue_src = true
  ) {
  }
  public function cond($pv)
  {
    return $this->pk . ' = ' . $pv;
  }
  public function set_inp(
    $id,
    $name
  ) {
    $file = uploadFile_secure($name, $this->ms, $this->at, $this->pf);
    if ($file) {
      $this->rem($id);
      return $this->set($id, $file);
    }
  }
  private function set(
    $id,
    $v
  ) {
    $temp = $this->tbl->UPDATE($this->cond($id))->Set($this->name . " = ?")->Run([$v]);
    return $temp;
  }
  function get_url($id)
  {
    return urlOfUpload($this->val($id));
  }

  function rem($id)
  {
    if ($this->has($id)) {
      unlinkUpload($this->val($id));
      return $this->set($id, 'NULL');
    }

  }

  function get_img(
    $id,
    $cattrs = '',
    $echo = false,
  ) {
    $purl =
      $this->get_url($id);
    return imageComponent($purl, $this->cattrs . $cattrs, $this->undefined, $echo, $this->ue_src);
  }

  function has($id)
  {
    $_purl = $this->get_url($id);
    $purl = $_SERVER['DOCUMENT_ROOT'] . regular_url($_purl);
    return file_exists($purl) && $this->get_url($id);
  }

  function val($id)
  {
    return $this->tbl->SELECT([$this->name])->WHERE($this->cond($id))->Run()->fetchColumn();
  }
}