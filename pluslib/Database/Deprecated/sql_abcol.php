<?php
namespace pluslib\Database\Deprecated;

defined('ABSPATH') || exit;

/**
 * !NOT RECOMMENDED TO USE!
 * @deprecated
 */
class sql_abcol
{
  public function __construct(
    public readonly string $name, // Colname
    public readonly string|null $val, // ColVal
    public readonly int $ms,
    public readonly array $at,
    public readonly string $pf,
  ) {
  }
  function get_url()
  {
    return urlOfUpload($this->val);
  }

  function get_img($cattrs = '', $undefined = '/default_uploads/unknown.png', $echo = false, $ue_src = true)
  {
    $purl =
      $this->get_url();
    return imageComponent($purl, $cattrs, $undefined, $echo, $ue_src);
  }

  function has()
  {
    $_purl = $this->get_url();
    $purl = $_SERVER['DOCUMENT_ROOT'] . regular_url($_purl);
    return file_exists($purl) && $this->get_url();
  }
}
