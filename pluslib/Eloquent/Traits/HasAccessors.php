<?php
namespace pluslib\Eloquent\Traits;

use pluslib\Collections\Arr;
use pluslib\Database\Expression;

defined('ABSPATH') || exit;

use function ucwords;

trait HasAccessors
{

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
}