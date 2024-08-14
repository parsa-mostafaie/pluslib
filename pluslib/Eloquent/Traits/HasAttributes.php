<?php
namespace pluslib\Eloquent\Traits;

use pluslib\Collections\Arr;
use pluslib\Database\Expression;

defined('ABSPATH') || exit;

trait HasAttributes
{
  /**
   * Default data for a model instance that not loaded from database
   * @var array
   */
  protected $defaultData = array();

  /**
   * The objects attributes
   * @var array
   */
  protected $_data = array();

  /**
   * The objects attributes (changes)
   * @var array
   */
  protected $_magicProperties = array();

  /* Methods */
  /**
   * Apply changes to data
   * @return array
   */
  protected function _mergedAttributes()
  {
    return array_merge($this->_data, $this->_magicProperties);
  }

  protected function _changes()
  {
    $data = $this->_data;
    $mp = $this->_magicProperties;

    $comp = function ($a, $b) {
      return $a === $b ? 0 : 1;
    };

    return array_udiff_assoc($mp, $data, $comp);
  }

  /**
   * Escapes keys/values of magic properties array
   * @return array       result of the load
   */
  protected function _escapedAttributes()
  {
    $props = $this->_changes();

    $normal = collect($props);
    $normal = $normal->map(fn($v) => $v instanceof Expression ? $v : expr('?'))->all();

    $data = array_values(array_filter($props, fn($val) => !$val instanceof Expression));

    return [$normal, $data];
  }

  /**
   * Returns Original (From db) Value of a field (= attribute = prop = data)
   * @return mixed
   */
  public function getOriginal($field)
  {
    $field = $this->_getFieldName($field);
    return $this->_data[$field] ?? null;
  }

  /**
   * find if the object has a property
   * @param  string  $name field name
   * @return boolean       result
   */
  public function hasAttribute($name)
  {
    return $this->$name !== null;
  }

  /**
   * Returns: model instance is changed?
   * 
   * @return bool
   */
  public function changed()
  {
    return $this->_mergedAttributes() !== $this->_data;
  }

  /**
   * Sets a field
   * @return static $this
   */
  public function setAttribute($field, $value)
  {
    $field = $this->_getFieldName($field);

    if ($this->loaded && $field == $this->id_field) {
      throw new \Exception("Setting field `$field` is not allowed in Models of type " . static::class);
    }

    $this->_magicProperties[$field] = $value;

    return $this;
  }


  /**
   * Returns Value of a attribute
   * 
   * @param string $attribute
   * @return mixed
   */
  public function getAttribute($attribute)
  {
    $attribute = $this->_getFieldName($attribute);

    if (!isset($this->_mergedAttributes()[$attribute])) {
      return $this->loadRelation($attribute);
    }

    return $this->_mergedAttributes()[$attribute] ?? null;
  }

  /**
   * Fills (Mass Assign) Model Using Attributes
   * 
   * @param array $attributes
   * @return static $this
   */
  public function fill($attributes)
  {
    $filtered = $this->_filter($attributes);

    foreach ($filtered as $n => $v) {
      $this->$n = $v;
    }

    return $this;
  }

  /**
   * Increments a attribute
   * 
   * @param string $attribute
   * @param int $amount
   */
  public function increment($attribute, $amount = 1)
  {
    $this->$attribute = expr(escape_col($attribute) . " + $amount");

    return $this;
  }

  /**
   * Decrement a attribute
   * 
   * @param string $attribute
   * @param int $amount
   */
  public function decrement($attribute, $amount = 1)
  {
    $this->$attribute = expr(escape_col($attribute) . " - $amount");

    return $this;
  }
}