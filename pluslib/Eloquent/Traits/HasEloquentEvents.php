<?php
namespace pluslib\Eloquent\Traits;

trait HasEloquentEvents
{

  /**
   * function to be run prior to db insert
   */
  protected function _precreate()
  {
  }

  /**
   * function to be run after db insert
   * @param boolean $result result of the db query
   */
  protected function _postcreate($result)
  {
  }

  /**
   * function to be run before a db update
   */
  protected function _preupdate()
  {
  }

  /**
   * function to be run after a db update
   * @param  boolean $result db query result
   */
  protected function _postupdate($result)
  {
  }

  /**
   * function to be run prior to db delete
   */
  protected function _predelete()
  {
  }

  /**
   * function to be run after the db delete
   * @param  boolean $result result of the db query
   */
  protected function _postdelete($result)
  {
  }

  /**
   * function to rewrite the output of toArray() if required
   * @param  array $output array from toArray()
   * @return array         
   */
  protected function _postarray($output)
  {
    return $output;
  }

  /**
   * function to be run prior to loading data
   */
  protected function _preload()
  {
  }
  /**
   * function to be run prior to loading data
   * @param boolean $result result of the load
   */
  protected function _postload($result)
  {
  }
}