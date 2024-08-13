<?php
namespace pluslib\Eloquent;

defined('ABSPATH') || exit;

use pluslib\Database\Query\Select as BaseSelect;
use pluslib\Database\Table;

class Select extends BaseSelect
{
  protected $with = [];

  public function __construct(
    Table $table,
    string|array $cols = ['*'],
    protected string $model
  ) {
    parent::__construct($table, $cols);
  }

  public function toBase()
  {
    return $this;
  }

  public function with($properties)
  {
    $properties = is_string($properties) ? func_get_args() : $properties;

    $this->with = array_merge($this->with, $properties);

    return $this;
  }

  public function getArray($params = [])
  {
    return array_map(function ($v) {
      /**
       * @var BaseModel
       */
      $instance = new $this->model;

      $instance->fromArray($v);

      $instance->loadRelations($this->with);

      return $instance;
    }, parent::getArray($params));
  }

  /**
   * Save a new model and return the instance.
   *
   * @param  array  $attributes
   * @return BaseModel
   */
  public function create(array $attributes = [])
  {
    return tap($this->newModelInstance($attributes), function ($instance) {
      $instance->save();
    });
  }

  /**
   * Get the first record matching the attributes. If the record is not found, create it.
   *
   * @param  array  $attributes
   * @param  array  $values
   * @return BaseModel
   */
  public function firstOrCreate(array $attributes = [], array $values = [])
  {
    if (!is_null($instance = (clone $this)->where($attributes)->first())) {
      return $instance;
    }

    return $this->create(array_merge($attributes, $values));
  }
  public function newModelInstance($attributes): BaseModel
  {
    return (new $this->model)->fill($attributes);
  }
}