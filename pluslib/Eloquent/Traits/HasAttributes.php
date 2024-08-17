<?php
namespace pluslib\Eloquent\Traits;

use Closure;
use pluslib\Collections\Arr;
use pluslib\Database\Expression;
use ReflectionMethod;
use ReflectionNamedType;
use pluslib\Eloquent\Attribute;

defined('ABSPATH') || exit;

trait HasAttributes
{
  use HasAccessors;

  /**
   * Default data for a model instance that not loaded from database
   * @var array
   */
  protected $defaultData = array();

  /**
   * Cache for attribute Mutators
   * 
   * @var array
   */
  protected static $attributeMutatorCache = [];

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
    return $this->_magicProperties !== $this->_data;
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

    if ($this->hasSetMutator($field)) {
      return $this->getAttributeMutator($field)->set($value);
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
    if (!$attribute) {
      return null;
    }

    $attribute = $this->_getFieldName($attribute);

    if (isset($this->_magicProperties[$attribute])) {
      return $this->_magicProperties[$attribute] ?? null;
    }

    if ($this->hasGetMutator($attribute)) {
      return $this->getAttributeMutator($attribute)->get();
    }

    if ($this->hasAccessor($attribute)) {
      return $this->callAccessor($attribute);
    }

    return $this->loadRelation($attribute);
  }

  /**
   * Fills (Mass Assign) Model Using Attributes
   * 
   * @param array|Closure $attributes
   * @return static $this
   */
  public function fill($attributes)
  {
    if ($attributes instanceof Closure) {
      $attributes($this);
      return $this;
    }

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

  public function hasAttributeMutator($key, $echo = false)
  {
    if (isset(static::$attributeMutatorCache[get_class($this)][$key])) {
      return static::$attributeMutatorCache[get_class($this)][$key];
    }

    if (!method_exists($this, $method = camelcase($key))) {
      return static::$attributeMutatorCache[get_class($this)][$key] = false;
    }

    $returnType = (new ReflectionMethod($this, $method))->getReturnType();

    return static::$attributeMutatorCache[get_class($this)][$key] =
      $returnType instanceof ReflectionNamedType &&
      $returnType->getName() === Attribute::class;
  }

  public function getAttributeMutator($key): ?Attribute
  {
    if (!$this->hasAttributeMutator($key)) {
      return null;
    }

    return $this->{camelcase($key)}();
  }

  public function hasSetMutator($key)
  {
    if (!$this->hasAttributeMutator($key)) {
      return false;
    }

    return $this->getAttributeMutator($key)->hasSetter();
  }

  public function hasGetMutator($key)
  {
    if (!$this->hasAttributeMutator($key)) {
      return false;
    }

    return $this->getAttributeMutator($key)->hasGetter();
  }
}