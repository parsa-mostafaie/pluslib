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
  protected function _mergedProps()
  {
    return array_merge($this->_data, $this->_magicProperties);
  }

  /**
   * Return Diffrence of _data and merged_props
   * 
   * @return array
   */
  protected function _changes()
  {
    $data = $this->_data;
    $mp = $this->_mergedProps();

    $comp = fn($a, $b) => $a == $b ? 0 : -1;

    return array_diff_uassoc($mp, $data, $comp);
  }

  /**
   * Escapes keys/values of magic properties array
   * @return array       result of the load
   */
  protected function _escapedMagicProps()
  {
    $props = $this->_filtered_changes();

    $normal = collect($props);
    $normal = $normal->map(fn($v) => $v instanceof Expression ? $v : expr('?'))->all();

    $data = array_values(array_filter($props, fn($val) => !$val instanceof Expression));

    return [$normal, $data];
  }

  /**
   * Returns Original (From db) Value of a field (= attribute = prop = data)
   * @return mixed
   */
  public function _getOriginal($field)
  {
    $field = $this->_getFieldName($field);
    return $this->_data[$field] ?? null;
  }

  /**
   * find if the object has a property
   * @param  string  $name field name
   * @return boolean       result
   */
  public function _hasProperty($name)
  {
    return array_key_exists($this->_getFieldName($name), $this->_mergedProps());
  }

  /**
   * Returns: model instance is changed?
   * 
   * @return bool
   */
  public function changed()
  {
    return $this->_mergedProps() !== $this->_data;
  }

  /**
   * Sets a field
   * @return static $this
   */
  public function _setField($field, $value)
  {
    $field = $this->_getFieldName($field);

    if (!in_array($field, $this->fillable)) {
      throw new \Exception("Setting field `$field` is not allowed in Models of type " . static::class);
    }

    $this->_magicProperties[$field] = $value;

    return $this;
  }


  /**
   * Returns Value of a field (= attribute = prop = data)
   * 
   * @param string $field
   * @return mixed
   */
  public function _get($field)
  {
    $field = $this->_getFieldName($field);
    return $this->_mergedProps()[$field] ?? null;
  }
}