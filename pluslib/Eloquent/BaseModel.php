<?php
namespace pluslib\Eloquent;

use ArrayAccess;
use pluslib\Database\Expression;
use pluslib\Eloquent\Traits\HasAttributes;
use pluslib\Eloquent\Traits\HasFilteredAttributes;
use Sql_DB;
use pluslib\Database\Table;
use \Exception;
use pluslib\Eloquent\Traits\HasRelations;
use JsonSerializable;
use pluslib\Collections\Arr;
use pluslib\Database\Query\Helpers as QueryBuilding;
use pluslib\Eloquent\Traits\HasAppends;
use pluslib\Eloquent\Traits\HasEloquentEvents;
use pluslib\Eloquent\Traits\HasTimestamps;
use pluslib\Eloquent\Traits\HasTranslation;
use pluslib\Eloquent\Traits\HidesAttributes;

defined('ABSPATH') || exit;
/**
 * BaseModel class
 *
 * @source https://github.com/brucealdridge/BaseModel Forked From brucealdridge's Code
 */

/**
 * A Base database model to extend with tables
 */
abstract class BaseModel implements ArrayAccess, JsonSerializable
{
  use
    HasAttributes,
    HasTranslation,
    HasEloquentEvents,
    HasAppends,
    HidesAttributes,
    HasFilteredAttributes,
    HasRelations,
    HasTimestamps;

  /**
   * when enabled, delete/insert/update will denied!
   * @var bool 
   */
  protected $readonly = false;

  /**
   * Relations to eager load when hydrating
   * 
   * @var array
   */
  protected $with = [];

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
   * Whether or not the object has been loaded from the database
   * @var boolean
   */
  public $loaded = false;  // a record/object is loaded

  /**
   * Indicates that model is created within object's lifecycle
   * 
   * @var boolean
   */
  public $wasRecentlyCreated = false;

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
  protected function _newSelect($cols = '*')
  {
    return new Select(static::_getTable(), $cols, static::class);
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
   * alias for all()
   * 
   * @return \pluslib\Collections\Collection  array of objects
   */
  public static function getAll()
  {
    return static::all();
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
  public static function select($cols = '*')
  {
    return (new static)->_newSelect($cols);
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
    return new static($this->_oid());
  }

  public function refresh()
  {
    return $this->load($this->_oid());
  }

  //! crud
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

    $result = static::_getTable()->DELETE($this->id_field . ' = ?')->Run([$this->_oid()]);
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
    $data[] = $this->_oid();

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

    $this->loaded = true;

    $this->wasRecentlyCreated = true;

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
   * magic method to handle all unlisted calls. Currently binds set{$VAR}() and get{$VAR}() functions
   * @param  string $method    
   * @param  mixed $parameters 
   * @return mixed             
   */
  public function __call($method, $parameters)
  {
    //for this to be a setSomething or getSomething, the name has to have
    //at least 4 chars as in, setX or getX
    if (strlen($method) < 4) {
      return $this->_newSelect()->$method(...$parameters);
    }

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

    return $this->_newSelect()->$method(...$parameters);
  }

  /**
   * handles the magic setting of parameters
   */
  public function __set($property, $value)
  {

    $this->_setField($property, $value);
  }

  /**
   * handles the magic getting of parameters
   */
  public function __get($property)
  {
    $property = $this->_getFieldName($property);
    if (!isset($this->_mergedProps()[$property])) {
      return $this->loadRelation($property);
    }
    return $this->_get($property);
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

  /**
   * Returns original value of id field
   * 
   * @return mixed
   */
  public function _oid()
  {
    return $this->_getOriginal($this->id_field);
  }

  //! Relations
  /**
   * Returns a select with a initial with
   *
   * @param  string|array $properties the fields
   * @return Select       
   */
  public static function with($properties)
  {
    return static::select()->with(is_string($properties) ? func_get_args() : $properties);
  }

  /**
   * Eager Loads relation within `with` Array
   *
   * @return array
   */
  protected function eagerLoads()
  {
    return $this->loadRelations($this->with);
  }

  //! Convert from/to Array

  /**
   * convert the object to an array
   * @return array array of all fields in object
   */
  public function toArray()
  {
    $arr = array_merge($this->translationsToArray(false), $this->_related, $this->appendsToArray());

    return $this->_postarray($arr);
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

    $this->eagerLoads();

    $this->_postload($this->loaded);

    return $this->loaded;
  }

  // Array Access
  function offsetExists(mixed $offset): bool
  {
    return $this->_hasProperty($offset);
  }

  function offsetGet(mixed $offset): mixed
  {
    return $this->$offset;
  }

  function offsetSet(mixed $offset, mixed $value): void
  {
    $this->_setField($offset, $value);
  }

  function offsetUnset(mixed $offset): void
  {
    unset($this->_magicProperties[$offset]);
  }

  function __unset(mixed $offset)
  {
    $this->offsetUnset($offset);
  }

  // Json Serialize
  function jsonSerialize(): mixed
  {
    return $this->toArray();
  }

  // Call static
  public static function __callStatic($method, $args)
  {
    return (new static)->$method(...$args);
  }
}

// TODO: Implement The Many-to-many
// TODO: make properties case-insensitive
// TODO: Implement Uploadeds