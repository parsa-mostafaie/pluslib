<?php
namespace pluslib\View;

class ViewNotFoundException extends \Exception
{
  public function __construct($view, $code = 0, \Exception $previous = null)
  {
    parent::__construct("The view `$view` was not found!", $code, $previous);
  }
}