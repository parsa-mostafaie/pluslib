<?php
namespace pluslib\MVC;

use Sql_DB;
use Sql_Table;
use \Exception;
use pluslib\helpers\QueryBuilding;
use \selectQueryCLASS;

defined('ABSPATH') || exit;
/**
 * BaseModel class
 *
 * @source https://github.com/brucealdridge/BaseModel Forked From brucealdridge's Code
 */

/**
 * A Base database model to extend with tables
 */
class BaseModel
{
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
   * related records (loaded through relationships)
   * @var array
   */
  protected $_related;

  /**
   * Whether or not the object has been loaded from the database
   * @var boolean
   */
  public $loaded = false;  //a record/object is loaded

  /**
   * The objects attributes
   * @var array
   */
  protected $_magicProperties = array();

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
   * @return Sql_Table|null
   */
  protected static function _getTable()
  {
    return db()->table((new static)->table);
  }

  /**
   * select query class instance of model
   * @return selectQueryCLASS
   */
  protected static function _newSelect()
  {
    return new selectQueryCLASS(static::_getTable(), modelType: static::class);
  }

  /**
   * Escapes keys of magic properties arry
   * @return array       result of the load
   */
  protected function _escapedMagicProps()
  {
    return QueryBuilding::NormalizeArray($this->_magicProperties);
  }

  //! Selecting static methods
  /**
   * Load a record from the database with conditions
   * @param  string $where conditions for a where statement eg `type=1`
   * @param  array  $data  parameters for the conditions, eg `type=?` for conditions and array(1) for $data
   * @return static       result of the load
   */
  public static function getWithWhere(string $where, $data = null)
  {
    $query = (new selectQueryCLASS(static::_getTable(), modelType: static::class))
      ->where($where ?? '1=1');
    $result = $query->LIMIT('1')->Run($data)->fetch()[(new static)->id_field];

    return static::find($result);
  }

  /**
   * Load a record from the database with id
   * @param  $id id of record
   * @return static       result of the search
   */
  public static function find($id)
  {
    return new static($id);
  }

  /**
   * get all objects as an array from the database, optionally based on conditions
   * @param  string $conditions conditions for a where statement eg `type=1`
   * @param  array  $data       parameters for the conditions, eg `type=?` for conditions and array(1) for $data
   * @return array              array of objects
   */
  public static function getAll(string|null $conditions = null, $data = array())
  {
    $query = static::_newSelect()->where($conditions ?? '1=1');
    $rows = $query->getArray($data);
    return $rows;
  }

  public static function select()
  {
    return new selectQueryCLASS((new static)->_getTable(), '*', static::class);
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

  //! loading state
  /**
   * load a specific row from the database
   * @param  integer $id id of the row to load from the database
   * @return boolean     result of the load
   */
  public function load($id)
  {
    $result = static::_getTable()->SELECT()->WHERE($this->id_field . '=?')->Run([$id])->fetch(\PDO::FETCH_ASSOC);

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

  //! crud
  /**
   * delete the object from the database
   * @return boolean result of the query
   */
  public function delete()
  {
    $this->_predelete();
    $result = static::_getTable()->DELETE($this->id_field . ' = ?')->Run([$this->{$this->id_field}]);
    $this->_postdelete($result);
    return $result;
  }

  /** 
   * run an UPDATE on the object in the db
   * @return boolean result
   */
  public function update()
  {
    $this->_preupdate();
    $mprops = $this->_escapedMagicProps();
    $data = array_values($this->_magicProperties);

    $query = static::_getTable()->UPDATE($this->id_field . ' = ?')->fromArray($mprops);
    $data[] = $this->{'get' . $this->id_field}();

    $result = $query->Run($data);
    $this->_postupdate($result);
    return $result;
  }

  /**
   * run a database insert
   * @return integer primary key of inserted row (if available)
   */
  public function create()
  {
    $this->_precreate();
    $data = array_values($this->_magicProperties);
    $mprops = $this->_escapedMagicProps();
    $result = static::_getTable()->INSERT([])->fromArray($mprops)->Run($data);
    if (!isset($this->_magicProperties['id']) || !$this->_magicProperties['id']) {
      $id = db()->lastInsertId();
      $this->{'set' . $this->id_field}($id);
    }
    $this->loaded = true;
    $this->_postcreate($result);
    $this->load($id); // load all columns from db (also what not setted)
    return $this->{$this->id_field};
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

    //take last chars and convert to lower to get required property
    $suffix = strtolower(substr($method, 3));

    if (isset($this->translation[$suffix]))
      $suffix = $this->translation[$suffix];

    if ($prefix == 'get') {
      if ($this->_hasProperty($suffix) && count($parameters) == 0) {
        return $this->_magicProperties[$suffix];
      } else {
        throw new Exception('Getter does not exist (' . $suffix . ')');
      }
    }

    if ($prefix == 'set') {
      if (count($parameters) < 3) {
        $this->_magicProperties[$suffix] = $parameters[0];
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
    if (isset($this->translation[$property])) {
      $property = $this->translation[$property];
    }
    if ($property == $this->id_field && $this->loaded) {
      throw new Exception(
        'Setting id (' . $this->id_field . ') In a loaded Model instance may cause bugs!'
      );
    }
    $this->_magicProperties[$property] = $value;
  }

  /**
   * handles the magic getting of parameters
   */
  public function __get($property)
  {
    if (isset($this->translation[$property])) {
      $property = $this->translation[$property];
    }
    if (!isset($this->_magicProperties[$property])) {
      return $this->loadRelations($property);
    }
    return isset($this->_magicProperties[$property]) ? $this->_magicProperties[$property] : null;
  }

  /**
   * find if the object has a property
   * @param  string  $name field name
   * @return boolean       result
   */
  public function _hasProperty($name)
  {
    if (isset($this->translation[$name])) {
      $name = $this->translation[$name];
    }
    return array_key_exists($name, $this->_magicProperties);
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
    foreach ($this->_magicProperties as $key => $value) {
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
    $this->_magicProperties = array();

    // clear out cached relations
    $this->_related = array();


    $this->loaded = false;
    if (is_array($row) || is_object($row)) {
      foreach ($row as $k => $v) {
        $this->$k = $v;
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
      $h = '<table style="margin: 5px 10px; border: solid 1px;" width="50%"><caption style="font-size: 1.2em; color: #666;">' . htmlspecialchars($this->table) . ' (' . get_class($this) . ') #' . $this->id . '</caption>';
      if (count($this->relationships)) {
        $h .= '<tr><th colspan=2>Relationships</th></tr>';
        if (count($this->_related)) {
          foreach ($this->_related as $prop => $r) {
            $h .= '<tr><td style="font-weight: bold;" valign="top">' . htmlspecialchars($prop) . '</td><td>' . (is_array($r) ? count($r) . ' records' . (count($r) ? ' (' . get_class($r[0]) . ')' : '') : get_class($r)) . '</td></tr>';
          }
        }
        if (count($this->relationships) > count($this->_related)) {

          foreach ($this->relationships as $prop => $r) {
            if (!isset($this->_related[$prop])) {
              $h .= '<tr><td style="font-weight: bold;" valign="top">' . htmlspecialchars($prop) . '</td><td>' . (is_callable($r) ? 'Unknown Function' : $r[1]) . '</td></tr>';
            }
          }
        }
        $h .= '<tr><th colspan=2>Fields</th></tr>';
      }

      foreach ($this->_magicProperties as $key => $value) {
        $h .= '<tr>';
        $h .= '<td style="font-weight: bold;" valign="top">' . htmlspecialchars(in_array($key, $this->translation) ? array_search($key, $this->translation) : $key) . '</td><td>' . htmlspecialchars($value) . '</td>';
        $h .= '</tr>';
      }
      $h .= '</table>';
    }
    echo $h;
  }
}

class usersModel extends BaseModel
{
  protected $table = 'users';
  protected $id_field = 'ID';
}

// TODO: Create models.defaults.php
// TODO: Implement The Many-to-many
// TODO: make properties case-insensitive