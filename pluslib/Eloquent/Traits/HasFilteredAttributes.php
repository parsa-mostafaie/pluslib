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
   * Filters Changes using $fillable and $guardeds (IN FUTURE)
   *
   *  @return array
   */
  protected function _filtered_changes()
  {
    return Arr::only($this->_changes(), $this->fillable);
  }
}