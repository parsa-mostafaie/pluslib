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
            $this->_related[$property] = $this->belongsTo($class, $field)->first();
            break;
          case self::HAS_MANY:
            $this->_related[$property] = $this->hasMany($class, $field)->get();
            break;
          case self::HAS_ONE:
            $this->_related[$property] = $this->hasOne($class, $field)->first();
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
   * Returns an belongs-to relations select query
   * 
   * @return \pluslib\Eloquent\Select
   */
  public function belongsTo($class, $foreign_key = null, $owner_key = null)
  {
    $tmp = new $class;

    $foreign_key ??= strtolower(basename($class) . '_' . $tmp->id_field);
    $owner_key ??= strtolower($tmp->id_field);

    return (new $class)->where($owner_key, $this->$foreign_key)->take(1);
  }

  /**
   * Returns an has-one relations select query
   * 
   * @return \pluslib\Eloquent\Select
   */
  public function hasOne($class, $foreign_key = null, $local_key = null)
  {
    return $this->hasOneOrMany($class, $foreign_key, $local_key)->take(1);
  }

  /**
   * Returns an has-many relations select query
   * 
   * @return \pluslib\Eloquent\Select
   */
  public function hasMany($class, $foreign_key = null, $local_key = null)
  {
    return $this->hasOneOrMany($class, $foreign_key, $local_key);
  }

  public function hasOneOrMany($class, $foreign_key = null, $local_key = null)
  {
    $tmp = new $class;

    $foreign_key ??= strtolower(basename(static::class) . '_' . $this->id_field);
    $local_key ??= $this->id_field;

    return $tmp->where($foreign_key, $this->$local_key);
  }


  /**
   * relationship Constants
   */
  const BELONGS_TO = 1;
  const HAS_ONE = 2;
  const HAS_MANY = 3;
  const MANY_MANY = 4; // NOT YET IMPLEMENTED
}