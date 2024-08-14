<?php
namespace pluslib\Eloquent\Traits;

use pluslib\Collections\Arr;
use pluslib\Database\Expression;

defined('ABSPATH') || exit;

use function ucwords;

trait HasAccessors
{
  /**
   * Accessors to include in toArray's result
   * 
   * @var array
   */
  protected $accessors = [];

  protected function getAccessorName($attribute)
  {
    $attribute = pascalcase($attribute);

    return "get{$attribute}Attribute";
  }

  protected function callAccessor($attribute)
  {
    if (!$this->hasAccessor($attribute))
      return null;

    return $this->{$this->getAccessorName($attribute)}();
  }

  protected function hasAccessor($attribute)
  {
    return method_exists($this, $this->getAccessorName($attribute));
  }

  protected function accessorsToArray(){
    $result = [];

    foreach ($this->accessors as $name => $fn) {
      if (is_numeric($name)) {
        $name = $fn;
      }

      $result[$name] = $this->$fn;
    }

    return $result;
  }
}