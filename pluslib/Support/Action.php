<?php
namespace pluslib\Support;

use function in_array;

class Action
{
  protected $_cb = [];

  public function add(callable $_cb)
  {
    if (!in_array($_cb, $this->_cb))
      $this->_cb[] = $_cb;

    return $this;
  }

  public function do()
  {
    foreach ($this->_cb as $_cb) {
      $_cb();
    }
  }
}