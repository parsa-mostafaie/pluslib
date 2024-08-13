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
   * Guarded fields
   * 
   * @var array
   */
  protected $guarded = [];

  /**
   * Filters using $fillable and $guarded
   *
   *  @return array
   */
  protected function _filter($arr = [])
  {
    return Arr::except(Arr::only($arr, $this->fillable), $this->guarded);
  }
}