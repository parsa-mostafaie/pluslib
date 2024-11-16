<?php
namespace pluslib\Eloquent;

use Exception;
use pluslib\HTTP\HTTPException;

class ModelNotFoundException extends HTTPException {

  public function __construct($model, Exception $previous = null)
  {
    parent::__construct(404, "Not Found!", [], $previous);
  }
}