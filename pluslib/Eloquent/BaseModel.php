<?php
namespace pluslib\Eloquent;

use pluslib\Database\Expression;
use Sql_DB;
use pluslib\Database\Table;
use \Exception;
use pluslib\Database\Query\Select;
use pluslib\Database\Query\Helpers as QueryBuilding;

defined('ABSPATH') || exit;
/**
 * BaseModel class
 *
 * @source https://github.com/brucealdridge/BaseModel Forked From brucealdridge's Code
 */

/**
 * A Base database model to extend with tables
 */
abstract class BaseModel
{
  /**
   * when enabled, delete/insert/update will denied!
   * @var bool 
   */
  protected $readonly = false;

  /** 
   * Table name
   * @var string
   */
  protected $table = '';

  /**
   * Tables primary key
   * @var string
   */
  protected $id_field = 'id';

  /**
   * Translation of fields
   * array('table_id' => 'id') will allow you to map $obj->id calls to $obj->table_id
   * @var array
   */
  protected $translation = array();

  /**
   * Define the relationships between table fields and other objects to allow for autoloading
   * an array of links defined as below
   *    link => array(relationship, class, id_field)
   *    OR
   *    link => function ($this) { return $object }
   *
   * <code>
   * array(
   *       'teacher' => array(self::HAS_ONE, 'Teacher', 'classroom_id'),
   *       'students' => array(self::HAS_MANY, 'Student', 'classroom_id'),
   *       'school' => array(self::BELONGS_TO, 'School', 'school_id'),
   *       'parents' => function ($classroom) { $p = new Parent; $p->getWithWhere('SELECT * FROM parent ORDER BY rand()'); return $p; },
   *   );     
   * </code>
   * @var array
   */
  protected $relationships = array();

  /**
   * Default data for a model instance that not loaded from database
   * @var array
   */
  protected $defaultData = array();

  /**
   * related records (loaded through relationships)
   * @var array
   */
  protected $_related;

  /**
   * Whether or not the object has been loaded from the database
   * @var boolean
   */
  public $loaded = false;  // a record/object is loaded

  /**
   * The objects attributes
   * @var array
   */
  protected $_data = array();

  /**
   * The objects attributes (changes)
   * @var array
   */
  protected $_magicProperties = array();


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

  /**
   * relationship Constants
   */
  const BELONGS_TO = 1;
  const HAS_ONE = 2;
  const HAS_MANY = 3;
  const MANY_MANY = 4; // NOT YET IMPLEMENTED

  //! Static Methods
  /**
   * Sql_Table instance of table (null if not found!)
   * @return Table|null
   */
  protected static function _getTable()
  {
    return db()->table((new static)->table);
  }

  /**
   * select query class instance of model
   * @return Select
   */
  protected static function _newSelect()
  {
    return new Select(static::_getTable(), static::_getTable()->primaryKey(), static::class);
  }

  /**
   * Apply changes to data
   * @return array
   */
  protected function _mergedProps()
  {
    return array_merge($this->_data, $this->_magicProperties);
  }

  /**
   * Escapes keys/values of magic properties array
   * @return array       result of the load
   */
  protected function _escapedMagicProps()
  {
    $props = $this->_mergedProps();
    $normal = collect($props);
    $normal = $normal->map(fn($v) => $v instanceof Expression ? $v : expr('?'))->all();

    $data = array_values(array_filter($props, fn($val) => !$val instanceof Expression));

    return [$normal, $data];
  }

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
  //! Selecting static methods
  /**
   * Returns a select query with initial where condition(s)
   * 
   * @param mixed $key
   * @param mixed $operator
   * @param mixed $value
   * 
   * @return Select
   */
  public static function where($key, $operator = null, $value = null)
  {
    return static::select()->where(cond(...func_get_args()));
  }

  /**
   * Load a record from the database with conditions
   * @param  string $where conditions for a where statement eg `type=1`
   * @param  array  $data  parameters for the conditions, eg `type=?` for conditions and array(1) for $data
   * @return static       result of the load
   */
  public static function getWithWhere(string $where, $data = null)
  {
    $query = static::_newSelect()
      ->where($where ?? '1=1');
    $result = $query->LIMIT('1')->Run($data)->fetch()[(new static)->id_field];

    return static::find($result);
  }

  /**
   * Load a record from the database with id
   * @param  $id id of record
   * @return static|null       result of the search
   */
  public static function find($id)
  {
    $v = new static($id);
    return $v->loaded ? $v : null;
  }

  /**
   * get all objects as an collection from the database, optionally based on conditions
   * @param  string $conditions               conditions for a where statement eg `type=1`
   * @param  array  $data                     parameters for the conditions, eg `type=?` for conditions and array(1) for $data
   * @return \pluslib\Collections\Collection  array of objects
   */
  public static function getAll(string|null $conditions = null, $data = array())
  {
    $query = static::_newSelect()->where($conditions ?? '1=1');
    $rows = $query->get($data);
    return $rows;
  }

  /**
   * get all objects as an collection from the database
   * 
   * @return \pluslib\Collections\Collection  collection of objects
   */
  public static function all()
  {
    return static::select()->get();
  }

  /**
   * select query class instance of model
   * @return Select
   */
  public static function select()
  {
    return static::_newSelect();
  }


  //! Constructors
  /**
   * Autoload an object on __construct
   * @param integer $id primary key
   */
  function __construct($id = null)
  {
    if ($this->id_field != 'id' && !isset($this->translation['id'])) {
      $this->translation['id'] = $this->id_field;
    }
    if (isset($id)) {
      $this->load($id);

      if (!$this->loaded) {
        $this->{$this->id_field} = $id;
      }
    }
  }

  //! Events
  /**
   * function to be run prior to db insert
   */
  protected function _precreate()
  {
    $this->_setCreateTimestamp();
    $this->_setUpdateTimestamp();
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
    $this->_setUpdateTimestamp();
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

  //! loading state
  /**
   * load a specific row from the database
   * @param  integer $id id of the row to load from the database
   * @return boolean     result of the load
   */
  public function load($id)
  {
    $result = static::_getTable()->SELECT()->WHERE($this->id_field . '=?')->LIMIT(1)->Run([$id])->fetch(\PDO::FETCH_ASSOC);

    $this->fromArray($result);

    return $this->loaded;
  }

  /**
   * is an row from the database loaded
   * @return boolean
   */
  public function loaded()
  {
    return $this->loaded;
  }

  public function fresh()
  {
    return new static($this->_id());
  }

  public function refresh()
  {
    return $this->load($this->_id());
  }

  //! crud
  /**
   * Returns: model instance is changed?
   * 
   * @return bool
   */
  public function changed()
  {
    return $this->_mergedProps() !== $this->_data;
  }

  /**
   * delete the object from the database
   * 
   * @throws Exception If Model is readonly
   * 
   * @return boolean result of the query
   */
  public function delete()
  {
    if ($this->readonly) {
      throw new Exception("Delete is not allowed in readonly model: " . static::class);
    }

    if (!$this->loaded) {
      throw new Exception("Can't delete a model that not currently loaded from database!");
    }


    if ($this->_predelete() === false) {
      return false; // cancel delete
    }

    $result = static::_getTable()->DELETE($this->id_field . ' = ?')->Run([$this->_id()]);
    $this->_postdelete($result);
    return $result;
  }

  /** 
   * run an UPDATE on the object in the db
   * 
   * @throws Exception if model is readonly
   * 
   * @return boolean result
   */
  public function update()
  {
    if ($this->readonly) {
      throw new Exception("Update is not allowed in readonly model: " . static::class);
    }

    if ($this->_preupdate() === false) {
      return false; // cancel update;
    }

    [$mp, $data] = $this->_escapedMagicProps();

    $query = static::_getTable()->UPDATE($this->id_field . ' = ?')->fromArray($mp);
    $data[] = $this->{'get' . $this->id_field}();

    $result = $query->Run($data);

    $this->_postupdate($result);
    $this->refresh();

    return $result;
  }

  /**
   * run a database insert
   * 
   * @throws Exception If model is readonly
   * 
   * @return integer primary key of inserted row (if available)
   */
  public function create()
  {
    if ($this->readonly) {
      throw new Exception("Create is not allowed in readonly model: " . static::class);
    }

    $this->_precreate();

    [$mp, $data] = $this->_escapedMagicProps();

    $result = static::_getTable()->INSERT([])->fromArray($mp)->Run($data);

    // if (!isset($this->_magicProperties['id']) || !$this->_magicProperties['id']) {
    //   $id = db()->lastInsertId();
    //   $this->{'set' . $this->id_field}($id);
    // }

    $this->loaded = true;
    $this->_postcreate($result);
    $this->load(db()->lastInsertId()); // load all columns from db (also what not setted)
    return $this->_id();
  }

  /**
   * insert or update record based on current state
   * @return mixed
   */
  public function save()
  {
    if ($this->loaded) {
      return $this->update();
    } else {
      return $this->create();
    }
  }


  //! get/set
  /**
   * Returns Original (From db) Value of a field (= attribute = prop = data)
   * @return mixed
   */
  public function _getOriginal($field)
  {
    $field = $this->_getFieldName($field);
    return $this->_data[$field] ?? null;
  }

  /**
   * Returns Value of a field (= attribute = prop = data)
   * 
   * @param string $field
   * @return mixed
   */
  public function _get($field)
  {
    $field = $this->_getFieldName($field);
    return $this->_mergedProps()[$field] ?? null;
  }

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
    }
    return $name;
  }


  /**
   * find if the object has a property
   * @param  string  $name field name
   * @return boolean       result
   */
  public function _hasProperty($name)
  {
    return array_key_exists($this->_getFieldName($name), $this->_mergedProps());
  }

  /**
   * find if the object has a property
   * 
   * @param string
   * 
   * @return boolean
   */
  public function __isset($name)
  {
    return $this->_hasProperty($name);
  }

  /**
   * Sets a field
   * @return static $this
   */
  public function _setField($field, $value)
  {
    $field = $this->_getFieldName($field);

    $this->_magicProperties[$field] = $value;

    return $this;
  }

  /**
   * magic method to handle all unlisted calls. Currently binds set{$VAR}() and get{$VAR}() functions
   * @param  string $method    
   * @param  mixed $parameters 
   * @return mixed             
   */
  public function __call($method, $parameters)
  {
    //for this to be a setSomething or getSomething, the name has to have
    //at least 4 chars as in, setX or getX
    if (strlen($method) < 4)
      throw new Exception('Method does not exist');

    //take first 3 chars to determine if this is a get or set
    $prefix = substr($method, 0, 3);

    //take last chars !(and convert to lower) to get required property
    $suffix = /*strtolower*/ (substr($method, 3));

    $suffix = $this->_getFieldName($suffix);

    if ($prefix == 'get') {
      if ($this->_hasProperty($suffix) && count($parameters) == 0) {
        return $this->_get($suffix);
      } else {
        throw new Exception('Getter does not exist (' . $suffix . ')');
      }
    }

    if ($prefix == 'set') {
      if (count($parameters) < 3) {
        $this->_setField($suffix, $parameters[0]);
        return $this;
      } else {
        throw new Exception('Setter does not exist');
      }
    }
  }

  /**
   * handles the magic setting of parameters
   */
  public function __set($property, $value)
  {
    $property = $this->_getFieldName($property);
    if ($property == $this->id_field && $this->loaded) {
      throw new Exception(
        'Setting id (' . $this->id_field . ') In a loaded Model instance may cause bugs!'
      );
    }
    $this->_setField($property, $value);
  }

  /**
   * handles the magic getting of parameters
   */
  public function __get($property)
  {
    $property = $this->_getFieldName($property);
    if (!isset($this->_mergedProps()[$property])) {
      return $this->loadRelations($property);
    }
    return $this->_mergedProps()[$property] ?? null;
  }

  /**
   * Returns value of id field
   * 
   * @return mixed
   */
  public function _id()
  {
    return $this->{$this->id_field};
  }

  //! Relations
  /**
   * return the specified relationships, either from the cache (if exists) or the db
   * @param  string $property the field / class
   * @return mixed            an object, array or null
   */
  protected function loadRelations($property)
  {
    if (isset($this->_related[$property])) {
      return $this->_related[$property];
    }
    if (isset($this->relationships[$property])) {
      if (is_callable($this->relationships[$property])) {
        $this->_related[$property] = $this->relationships[$property]($this);
      } else {
        list($relation, $class, $field) = $this->relationships[$property];
        switch ($relation) {
          case self::BELONGS_TO:
            $this->_related[$property] = new $class($this->$field);
            break;
          case self::HAS_MANY:
            $tmp = new $class;
            $this->_related[$property] = $tmp->getAll($field . '=?', array($this->id));
            break;
          case self::HAS_ONE:
            $tmp = new $class;
            $this->_related[$property] = $tmp->getWithWhere($field . '=?', array($this->id));
            break;
        }
      }
      return isset($this->_related[$property]) ? $this->_related[$property] : null;
    }

    return null;
  }

  //! Convert from/to Array
  /**
   * convert the object to an array
   * @return array array of all fields in object
   */
  public function toArray()
  {
    $output = array();
    foreach ($this->_mergedProps as $key => $value) {
      if (in_array($key, $this->translation)) {
        $output[array_search($key, $this->translation)] = $value;
      } else {
        $output[$key] = $value;
      }
    }
    return $this->_postarray($output);
  }

  /**
   * load in data from an array
   * @param  array $row load in all the data in the array into this object
   * @return boolean    result of the load
   */
  public function fromArray($row)
  {
    $this->_preload();

    // clear out saved data
    $this->_magicProperties = [];
    $this->_data = $this->defaultData ?? [];

    // clear out cached relations
    $this->_related = array();


    $this->loaded = false;
    if (is_array($row) || is_object($row)) {
      foreach ($row as $k => $v) {
        $this->_data[$k] = $v;
      }
      $this->loaded = true;
    }
    $this->_postload($this->loaded);
    return $this->loaded;
  }

  //! dumping
  /**
   * dump the object in a nice readable way
   */
  public function dump($nice = true)
  {
    if (!$nice) {
      echo '<pre>', var_dump($this), '</pre>';
    } else {
      $h = '<table style="margin: 5px 10px; border: solid 1px;" width="50%"><caption style="font-size: 1.2em; color: #666;">' . e($this->table) . ' (' . get_class($this) . ') #' . $this->id . '</caption>';
      if (count($this->relationships)) {
        $h .= '<tr><th colspan=2>Relationships</th></tr>';
        if (count($this->_related)) {
          foreach ($this->_related as $prop => $r) {
            $h .= '<tr><td style="font-weight: bold;" valign="top">' . e($prop) . '</td><td>' . (is_array($r) ? count($r) . ' records' . (count($r) ? ' (' . get_class($r[0]) . ')' : '') : get_class($r)) . '</td></tr>';
          }
        }
        if (count($this->relationships) > count($this->_related)) {

          foreach ($this->relationships as $prop => $r) {
            if (!isset($this->_related[$prop])) {
              $h .= '<tr><td style="font-weight: bold;" valign="top">' . e($prop) . '</td><td>' . (is_callable($r) ? 'Unknown Function' : $r[1]) . '</td></tr>';
            }
          }
        }
        $h .= '<tr><th colspan=2>Fields</th></tr>';
      }

      foreach ($this->_mergedProps() as $key => $value) {
        $h .= '<tr>';
        $h .= '<td style="font-weight: bold;" valign="top">' .
          e(in_array($key, $this->translation) ? array_search($key, $this->translation) : $key) .
          '</td><td><pre>' .
          ($value instanceof Expression ? '<b>Expression</b> ' . e($value->raw) : e($value))
          . '</pre></td>';
        $h .= '</tr>';
      }
      $h .= '</table>';
    }
    echo $h;
  }
}

// TODO: Implement The Many-to-many
// TODO: make properties case-insensitive
// TODO: Implement Uploadeds