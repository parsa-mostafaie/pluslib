<?php
namespace pluslib\Database;

class Expression
{
  public function __construct(public $raw)
  {
  }

  public function __tostring() {
    return $this->raw;
  }
}
