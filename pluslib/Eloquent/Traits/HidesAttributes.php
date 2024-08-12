<?php
namespace pluslib\Eloquent\Traits;

defined('ABSPATH') || exit;

trait HidesAttributes
{
  /**
   * Hidden fields from serialization
   * @var array
   */
  protected $hidden = [];

  /**
   * Determins that a key is hidden or not
   * 
   * @param mixed $key
   * @return bool
   */
  public function is_hidden($key)
  {
    return in_array($key, $this->hidden);
  }
}