<?php
namespace pluslib\Eloquent\Traits;

trait HasAppends
{
  /**
   * Attributes that should be included in toArray's result
   * 
   * @var array
   */
  protected $appends = [];

  /**
   * Calculates value of appends
   *
   * @return array
   */
  public function appendsToArray()
  {
    $result = [];

    foreach ($this->appends as $name => $fn) {
      if (is_numeric($name)) {
        $name = $fn;
      }

      $result[$name] = $this->$fn;
    }

    return $result;
  }
}