<?php
namespace pluslib\Eloquent\Traits;

use pluslib\Collections\Arr;

defined('ABSPATH') || exit;

trait HasFilteredAttributes
{
  /**
   * Fillable fields
   * 
   * @var array
   */
  protected $fillable = [];

  /**
   * Filters using $fillable and $guardeds (IN FUTURE)
   *
   *  @return array
   */
  protected function _filter($arr = [])
  {
    return Arr::only($arr, $this->fillable);
  }
}