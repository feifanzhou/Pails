<?php
include_once('../helpers/db_helper.php');

function __autoload($class_name) {
  include $class_name . '.php';
}

class ModelBase {
  public $id;
  public $created_at;
  public $updated_at;
  public $_custom_table_name;

  protected static $_validates = [];    // Properties to verify

  protected static $_one_of = [];     // For has_one
  protected static $_many_of = [];    // For has_many
  protected static $_belongs_to = [];   // For belongs_to

  public static function db_table_name() {
    if (!empty($_custom_table_name))
      return $_custom_table_name;
    //http://stackoverflow.com/a/506737/472768
    $class_name = get_called_class();
    return lcfirst($class_name) . 's';  // Pluralize
  }

  public static function init($attributes=[], $unprotected=false) {
    // http://stackoverflow.com/a/13532641/472768
    $i = new static;
    foreach ($attributes as $k => $v) {
      // Unprotected is currently not being used
      // Should be used to toggle input sanitation
      
      // Skip key if trying to set ID, created_at or updated_at
      if ($k == 'id' || $k == 'created_at' || $k == 'updated_at'){
        $class_name = get_called_class();
        $k = lcfirst($class_name).'_'.$k;
        $i -> $k = $v;
        continue;
      }
      // http://stackoverflow.com/a/4478690/472768
      if (property_exists(get_class($i), $k))
        $i -> $k = $v;
      // Else ignore or return error
    }
  return $i;
  }
  
  public static function create($attributes=[], $unprotected = false) {
      $i = static::init($attributes, $unprotected);
      $r = $i -> save();
      if ($r == false)
          return $r;
      return $i;
  }
  
  public function save($skipValidation = false) {
    $class = get_called_class();
    $class_name = lcfirst($class);
    $class_id = $class_name.'_id';
    $validate_f = $class_name.'_validate'; // e.g. user_validate()
    $create_f = $class_name.'_create'; //e.g. "user_create()"
    $update_f = $class_name.'_update'; //e.g. "user_update()"
        if (!$skipValidation) { //don't validate underscores but do validate class as first name
      if(method_exists($class,$validate_f)){
        if (!$class::$validate_f())    // Some validation failed
          return false;
      }
    }
    if (empty($this -> $class_id)) {    // Insert new object into database
      //special actions before creating new row
      if(method_exists($class,$create_f))
        if (!$class::$create_f()) 
          return false; // Some creation failed
        return insert($this -> db_table_name(), $this -> get_child_vars());
    }
    else {    // Update existing value
      //special actions before updating new row
      if(method_exists($class,$update_f))
        if (!$class::$update_f())   // Some creation failed
            return false;
      return update($this -> db_table_name(), $this -> get_child_vars());
    }
  }

  public function id() {
    $classname = lcfirst(get_called_class());
    $id_string = $classname . '_id';
    return $this -> $id_string;
  }

  public static function find($id) {
    $table = static::db_table_name();
    $q = "SELECT * FROM $table WHERE id=?";
    $db = dbConnect();
    $stmt = $db->prepare($q);
    bind($stmt, array($id));
    $stmt->execute();
    $result = fetch($stmt);
        if ($result) {
      //set id and timestamps
      $i = static::init($result[0]);
      $i->id = (isset($result[0]['id'])) ? $result[0]['id'] : NULL;
      $i->created_at = (isset($result[0]['created_at'])) ? $result[0]['created_at'] : NULL;
      $i->updated_at = (isset($result[0]['updated_at'])) ? $result[0]['updated_at'] : NULL;
            return $i;
        }
        else
            return false;
  }

  public static function validates($properties) {
    foreach($properties as $p)
      $_validates[] = $p;
  }

  public static function has_one($classname) {
    $called_class = get_class();
    if (!in_array($classname, $called_class::$_one_of))
      $called_class::$_one_of[] = lcfirst($classname);
  }
  public static function has_many($classname_plural) {
    // $_many_of[] = substr($classname_plural, 0, (strlen($classname_plural) - 1));
    $called_class = get_called_class();
    if (!in_array($classname_plural, $called_class::$_many_of))
      $called_class::$_many_of[] = lcfirst($classname_plural);
  }
  public static function belongs_to($classname) {
    $called_class = get_called_class();
    if (!in_array($classname, $called_class::$_belongs_to))
      $called_class::$_belongs_to[] = lcfirst($classname);
  }

  // http://stackoverflow.com/a/4478690/472768
  public function __get($property) {
    if (property_exists($this, $property))
      return $this -> $property;
    // TODO: Sanitize
    $called_class = get_called_class();
    if (in_array($property, $called_class::$_belongs_to)) {
      $foreign_key = $property . '_id';
      $db = dbConnect();
      $table_name = static::db_table_name();
      $self_id = $this -> id();
      $q1 = "SELECT $foreign_key FROM $table_name WHERE id=$self_id";
      $result = $db -> query($q1);
      if ($result) {
        $r = $result -> fetch_array();
        $foreign_id = $r[0];
        $foreign_class = ucfirst($property);
        return $foreign_class::find($foreign_id);
      }
      return NULL;
    }
    // TODO: Clean up duplicate with $_many_of
    else if (in_array($property, $called_class::$_one_of)) {
      $foreign_table = $property . 's';
      $self_id = $this -> id();
      $foreign_key = lcfirst($called_class) . '_id';
      $q = "SELECT * FROM $foreign_table WHERE $foreign_key=$self_id LIMIT 1";
      $result = $db -> query($q);
      if ($result) {
        $r = $result -> fetch_array();
        $c = ucfirst($property);
        return $c::init($r);
      }
      return NULL;
    }
    else if (in_array($property, $called_class::$_many_of)) {
      $foreign_table = $property; // Should already be pluralized
      $self_id = $this -> id();
      $foreign_key = lcfirst($called_class) . '_id';
      $q = "SELECT * FROM $foreign_table WHERE $foreign_key=$self_id LIMIT 1";
      $result = $db -> query($q);
      $rs = [];
      $foreign_class = substr($property, 0, (strlen($property) - 1)); // Assuming all plurals end in single 's'
      while ($r = $result -> fetch_array())
        $rs[] = $foreign_class::init($r);
      if (count($rs) > 0)
        return $rs;
      else
        return NULL;
    }
    return NULL;
  }

  public static function __callStatic($name, $arguments) {
    if (strpos($name, 'find_by_') !== false || (strpos($name, 'find_all_by_') !== false)) {
    if (strpos($name, 'find_by_') !== false){
      // Invoking find_by_* method
      $name = substr($name, 8);
      $limit = true;
    }else if (strpos($name, 'find_all_by_') !== false) {
      $name = substr($name, 12);
      $limit = false;
    }
    $params = explode('and', $name);
    for ($i = 0; $i < count($params); $i++) {
      if (strlen($params[$i]) == 0)
      break;
      if (substr($params[$i], 0, 1) == '_')
      $params[$i] = substr_replace($params[$i], '', 0, 1);
      if (substr($params[$i], -1, 1) == '_')
      $params[$i] = substr_replace($params[$i], '', -1, 1);
    }
    if (count($params) != count($arguments)){
      return false;
    }else{
      $bind_arguments = array();
      for($i=0; $i<count($params); $i++){
        $bind_arguments[$params[$i]] = $arguments[$i];
      }
    }
    $result = select(static::db_table_name(), $bind_arguments, $limit);
    if ($result && $limit) {// find by returns 1 object
      $r = $result;
      $o = static::init($r);
      $o->id = (isset($r['id'])) ? $r['id'] : NULL;
      $o->created_at = (isset($r['created_at'])) ? $r['created_at'] : NULL;
      $o->updated_at = (isset($r['updated_at'])) ? $r['updated_at'] : NULL;
      return $o;
    }elseif($result && !$limit){ // find by_all return array of object results
      $rs = array();
      foreach ($result as $r){
        $o = static::init($r);
        $o->id = (isset($r['id'])) ? $r['id'] : NULL;
        $o->created_at = (isset($r['created_at'])) ? $r['created_at'] : NULL;
        $o->updated_at = (isset($r['updated_at'])) ? $r['updated_at'] : NULL;
        $rs[] = $o;
      }
      return $rs;
    }
    else
      return false;
  }
}

  public function get_child_vars($invoke_class=false){
    $c = ($invoke_class) ? $invoke_class : $this;
    $refclass = new ReflectionClass($c); // http://stackoverflow.com/a/3902482
    $class_name = $refclass->getShortName();
    $class_string = lcfirst($class_name).'_';
    $p = array();
         //remove part of string if variables has class as first part and replace values if appropriate
    foreach ($refclass->getProperties() as $o){
      if($o->class == $refclass->name){
        $k = $o->name;
        $p[$k] = $this->$k;
        if ($class_string == substr($k,0,strlen($class_string))){
          $newkey = str_replace($class_string,'', $k);
          $p[$newkey] = $p[$k];
          unset($p[$k]);
          $k = $newkey;
        }
      }
    }
    return $p;
  }
}
?>