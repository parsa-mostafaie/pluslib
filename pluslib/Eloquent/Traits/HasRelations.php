<?php
namespace pluslib\Eloquent\Traits;

defined('ABSPATH') || exit;

trait HasRelations
{
  /**
   * Define the relationships between table fields and other objects to allow for autoloading
   * an array of links defined as below
   *    link => array(relationship, class, id_field)
   *    OR
   *    link => function ($this) { return $object }
   *
   * @var array
   */
  protected $relationships = array();

  /**
   * related records (loaded through relationships)
   * @var array
   */
  protected $_related;


  /**
   * Return the specified relationship, either from the cache (if exists) or the db
   * @param  string $property the field / class
   * @return mixed            an object, array or null
   */
  public function loadRelation($property)
  {
    if (isset($this->_related[$property])) {
      return $this->_related[$property];
    }
    if (isset($this->relationships[$property])) {
      if (is_callable($this->relationships[$property])) {
        $this->_related[$property] = $this->relationships[$property]($this);
      } else {
        list($relation, $class, $field) = $this->relationships[$property];
        switch ($relation) {
          case self::BELONGS_TO:
            $this->_related[$property] = new $class($this->$field);
            break;
          case self::HAS_MANY:
            $tmp = new $class;
            $this->_related[$property] = $tmp->where($field, expr('?'))->get([$this->_oid()]);
            break;
          case self::HAS_ONE:
            $tmp = new $class;
            $this->_related[$property] = $tmp->where($field, expr('?'))->take(1)->first();
            break;
        }
      }

      return $this->_related[$property] ?? null;
    }

    return null;
  }

  /**
   * Eager Loads relationships
   * @param  string|array $property the fields
   * @return array        array of loaded properties
   */
  public function loadRelations($properties)
  {
    $properties = is_string($properties) ? func_get_args() : $properties;
    $res = [];

    foreach ($properties as $property) {
      $res[$property] = $this->loadRelation($property);
    }

    return $res;
  }


  /**
   * relationship Constants
   */
  const BELONGS_TO = 1;
  const HAS_ONE = 2;
  const HAS_MANY = 3;
  const MANY_MANY = 4; // NOT YET IMPLEMENTED
}