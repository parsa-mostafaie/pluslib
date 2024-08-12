<?php
namespace pluslib\Eloquent\Traits;

defined('ABSPATH') || exit;

trait HasTimestamps
{

  /**
   * Last Entity's Update Field Name
   * @var string|null
   */
  const updated_at = 'updated_at';

  /**
   * Entity's Creation Date Field Name
   * @var string|null
   */
  const created_at = 'created_at';


  /**
   * Timestamps Enable State
   * @var bool
   */
  public $_timestamps = true;

  /**
   * Timestamps Enable State Globally
   * @var bool
   */
  protected static $timestamps = true;

  // Working with timestamps
  public static function withoutTimestamps(callable $c)
  {
    $t = static::$timestamps;
    static::$timestamps = false;
    $c();
    static::$timestamps = $t;
  }

  protected function usesTimestamps()
  {
    return $this->_timestamps && static::$timestamps;
  }

  protected function _setCreateTimestamp()
  {
    if ($this->usesTimestamps() && static::created_at) {
      $this->{static::created_at} = expr('current_timestamp()');
    }
  }

  protected function _setUpdateTimestamp()
  {
    if ($this->usesTimestamps() && static::updated_at) {
      $this->{static::updated_at} = expr('current_timestamp()');
    }
  }

  protected function _setTimestamps(){
    $this->_setCreateTimestamp();
    $this->_setUpdateTimestamp();
  }
}