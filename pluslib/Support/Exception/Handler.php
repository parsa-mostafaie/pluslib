<?php
namespace pluslib\Support\Exception;

use pluslib\Eloquent\ModelNotFoundException;
use pluslib\HTTP\HTTPException;
use Throwable;

class Handler
{
  public static function handle(Throwable $throwable)
  {
    error_log($throwable);

    if (!app()->isDebug() && !$throwable instanceof HTTPException) {
      $throwable = new HTTPException(500);
    }

    if ($throwable instanceof HTTPException) {
      view('pluslib::httpexception', ['status' => $throwable->code, 'message' => $throwable->message])->status($throwable->code)->setHeaders($throwable->headers)->send();

      die;
    }

    view('pluslib::exception', compact('throwable'))->status(500)->send();

    die;
  }
}