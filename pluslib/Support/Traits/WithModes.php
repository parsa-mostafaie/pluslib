<?php
namespace pluslib\Support\Traits;

trait WithModes
{

  function isDebug()
  {
    return config('app.debug_mode', true);
  }

}