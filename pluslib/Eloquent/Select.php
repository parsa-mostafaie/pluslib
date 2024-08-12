<?php
namespace pluslib\Eloquent;

defined('ABSPATH') || exit;

use pluslib\Database\Query\Select as BaseSelect;
use pluslib\Database\Table;

class Select extends BaseSelect
{
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

  public function getArray($params = [])
  {
    return array_map(function ($v) {
      $instance = new $this->model;

      $instance->fromArray($v);

      return $instance;
    }, parent::getArray($params));
  }
}