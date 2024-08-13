<?php
namespace pluslib\Eloquent;

defined('ABSPATH') || exit;

use pluslib\Database\Query\Select as BaseSelect;
use pluslib\Database\Table;

/**
 * @template TModel of BaseModel
 */
class Select extends BaseSelect
{
  protected $with = [];

  /**
   * @var TModel
   */
  protected $model;

  public function __construct(
    Table $table,
    string|array $cols = ['*'],
    $model
  ) {
    parent::__construct($table, $cols);

    $this->model = $model;
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

  /**
   * Fetchs array of models
   * 
   * @param array $params
   * @return TModel[]
   */
  public function getArray($params = [])
  {
    return array_map(function ($v) {
      return $this->model->newFromArray($v);
    }, parent::getArray($params));
  }

  /**
   * Save a new model and return the instance.
   *
   * @param  array  $attributes
   * @return TModel
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
   * @return TModel
   */
  public function firstOrCreate(array $attributes = [], array $values = [])
  {
    if (!is_null($instance = (clone $this)->where($attributes)->first())) {
      return $instance;
    }

    return $this->create(array_merge($attributes, $values));
  }

  /**
   * Create or update a record matching the attributes, and fill it with values.
   *
   * @param  array  $attributes
   * @param  array  $values
   * @return TModel
   */
  public function updateOrCreate(array $attributes, array $values = [])
  {
    return tap($this->firstOrCreate($attributes, $values), function ($instance) use ($values) {
      if (!$instance->wasRecentlyCreated) {
        $instance->fill($values)->save();
      }
    });
  }

  public function newModelInstance($attributes)
  {
    return $this->model->newInstance($attributes);
  }
}