<?php
namespace pluslib\HTTP;

class HTTPException extends \Exception
{
  public $message;
  public $code;
  public $headers;

  public function __construct($code, $message = '', $headers = [], \Exception $previous = null)
  {
    parent::__construct("$code: $message", $code, $previous);

    $this->code = $code;
    $this->message = $message ?: Response::getStatusPhrase($code);
    $this->headers = $headers;
  }
}