<?php
namespace pluslib\Eloquent\Traits;

defined('ABSPATH') || exit;

trait HasTranslation
{
  /**
   * Translation of fields
   * array('table_id' => 'id') will allow you to map $obj->id calls to $obj->table_id
   * @var array
   */
  protected $translation = array();

  /* Methods */
  /**
   * Returns final name of field
   * 
   * @param string $name
   * @return mixed
   */
  public function _getFieldName($name)
  {
    if (isset($this->translation[$name])) {
      $name = $this->translation[$name];
      return $this->_getFieldName($name);
    }
    return $name;
  }

  /**
   * Converts Translations and values into array
   * 
   * @param bool $withoutReals
   * @return array
   */
  public function translationsToArray($withoutReals = true)
  {
    $output = [];

    foreach ($this->_mergedAttributes() as $key => $value) {
      if (!$this->is_hidden($key)) {
        $withoutReals || ($output[$key] = $value);

        $aliases = array_keys($this->translation, $key);

        foreach ($aliases as $alias) {
          $output[$alias] = $value;
        }
      }
    }

    return $output;
  }
}