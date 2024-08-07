<?php
namespace pluslib\Database\Query;

defined('ABSPATH') || exit;

use pluslib\Database\Query\Helpers as QueryBuilding;
use pluslib\Database\Table;
use pluslib\Database\Condition;
use pluslib\Database\Query\Conditional;

class Delete extends Conditional
{
  public function __construct(public readonly Table $table)
  {
    parent::__construct();
  }

  public function Run($params = [])
  {
    return $this->table->db->execute_q(
      $this->Generate(),
      $params
    );
  }

  public function Generate()
  {
    $tbl = $this->table->name();
    $condition = $this->condition;
    return "DELETE FROM $tbl WHERE $condition";
  }
}