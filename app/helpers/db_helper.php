<?php

$_mySQLi = NULL;
function dbConnect() {
  $testing = true;
  global $_mySQLi;
  if ($_mySQLi)
    return $_mySQLi;
  $host = "localhost";
  $dbuser = "root";
  $dbpwd = "";
  $db = "db";
  $_mySQLi = (new mySQLi($host,$dbuser,$dbpwd,$db));
  return $_mySQLi;
}

//http://stackoverflow.com/a/793535 
function bind($stmt, $arguments){
  $success = false;
  $t = NULL;
  if($stmt instanceof mysqli_stmt){
    foreach($arguments as $key => $value){
      if(is_int($value) || preg_match('/^[0-9]*$/', $value))
        $t .= "i";
      else
        $t .= "s";
      $refs[$key] = &$arguments[$key]; //http://stackoverflow.com/a/2305363
    }
    call_user_func_array(
      array($stmt, 'bind_param'), 
      array_merge(
        array($t),
        $refs
      )
     );
     $success = true;
  }
  return $success;
}
  
function fetch($result){    
  $array = array();
  if($result instanceof mysqli_stmt){
    $result->store_result();
    $variables = array();
    $data = array();
    $meta = $result->result_metadata();
    while($field = $meta->fetch_field())
      $variables[] = &$data[$field->name];
    call_user_func_array(array($result, 'bind_result'), $variables);
    $i=0;
    while($result->fetch()){
      $array[$i] = array();
      foreach($data as $k=>$v)
        $array[$i][$k] = $v;
      $i++;
    }
  }
  elseif($result instanceof mysqli_result){
    while($row = $result->fetch_assoc())
      $array[] = $row;
  }
  return $array;
}

function allTables() {
  $db = dbConnect();
  // $result = $db -> query("SHOW TABLES LIKE '^((?!wp\_).)*$'");
  $result = $db -> query("SHOW TABLES");
  $names = array(); // PHP oddness—no overhead of function call
  while ($row = $result -> fetch_array()) {
    $names[] = $row[0];
  }
  return $names;
}

function columnsForTable($table) {
  $db = dbConnect();
  // $q = $db -> prepare("SHOW COLUMNS FROM ?");
  // $q -> bind_param('s', $table);
  // $q -> execute();
  // $result = $q -> get_result();
  $result = $db -> query("SHOW COLUMNS FROM $table");
  $names = array();
  while ($row = $result -> fetch_array()) {
    $names[] = $row[0];
  }
  return $names;
}

/**
 * schemaVersion
 * @return int Version of schema as determined by timestamps on migrations
 */
function schemaVersion() {
  /* $fh = fopen("../../db/schema.php-db", 'r');
  if ($fh == FALSE || feof($fh))
    return -1;

  $line = fgets($fh);
  // http://stackoverflow.com/a/8609840/472768
  $matches;
  $match = preg_match("/\d/", $line, $matches, PREG_OFFSET_CAPTURE);
  if ($match == 0)
    return -1;
  $schema_start = $matches[0][1];
  $schema_str = substr($line, $schema_start);
  return intval($schema_str); */
  $v = file_get_contents('../../db/.current_version');
  if ($v == FALSE)
    $v = 0;
  return intval($v);
}

/**
 * alreadyMigrated
 * @return Array list of migration names which have already been applied
 */
function alreadyMigrated() {
  $db_dir = "../../db/migrations";
  $paths = scandir($db_dir);
  if ($paths == FALSE)
    return;
  $names = array();
  $curr_schema = schemaVersion();
  foreach ($paths as $path) {
    $preg;
    if (preg_match("/\.php-db/", $path, $preg, PREG_OFFSET_CAPTURE) == 0)
      continue;
    $extension_pos = $preg[0][1];
    $name_parts = explode('_', $path);
    $version = intval($name_parts[0]);
    if ($version <= $curr_schema) {
      $name_ext = explode('.', $path);
      $names[] = $name_ext[0];
    }
  }
  return $names;
}

// TODO: Refactor duplicate code from alreadyMigrated()
/**
 * pendingMigrations
 * @return Array list of migration names which have not yet been applied
 */
function pendingMigrations() {
  $db_dir = "../../db/migrations";
  $paths = scandir($db_dir);
  if ($paths == FALSE)
    return;
  $names = array();
  $curr_schema = schemaVersion();
  foreach ($paths as $path) {
    $preg;
    if (preg_match("/\.php-db/", $path, $preg, PREG_OFFSET_CAPTURE) == 0)
      continue;
    $extension_pos = $preg[0][1];
    $name_parts = explode('_', $path);
    $version = intval($name_parts[0]);
    if ($version > $curr_schema) {
      $name_ext = explode('.', $path);
      $names[] = $name_ext[0];
    }
  }
  return $names;
}

function select($table_name, $arguments = [], $limit = true, $order = 'id'){
  $q = "SELECT * FROM $table_name WHERE ";
  $i=0;
  foreach($arguments as $k => $v){
    $s = $k . " = ?";
    if ($i < (count($arguments) - 1)){    // Not last one
      $s .= ' AND ';
    }
    $q .= $s;
    $i++;
  }
  $q .= " ORDER BY $order";
  if($limit)
    $q .= ' LIMIT 1';    // Should only be one result
  $db = dbConnect();
  $stmt = $db->prepare($q);
  bind($stmt, $arguments);
  $stmt->execute();
  $result = fetch($stmt);
  if($result && $limit){
    return $result[0]; //return only the one result
  }elseif($result && !$limit){
    return $result;//return an array of results
  }else{
    return NULL;
  }
}

function update($table_name, $arguments=[]){
  // TODO: Only update properties that have been changed
  if(isset($arguments['id'])){ //update with id, if not set, use class id
    $id = $arguments['id'];
    unset($arguments['id']);
  }else
    return false;
  $q = "UPDATE $table_name SET ";
  foreach ($arguments as $k => $v){
    if(strpos($k, '_') === 0 || $k == 'updated_at' || $k == 'created_at')
      unset($arguments[$k]);
    else
      $q .= "$k=?,";
  }
  $q .= "updated_at=now() WHERE id=?;";
  $arguments['id'] = $id; //ensure id is at the end of the array
  $db = dbConnect();
  $stmt = $db->prepare($q);
  bind($stmt, $arguments);
  return $stmt->execute();
}
  
function insert($table_name, $arguments=[]){
  $q = "INSERT INTO $table_name (created_at,";
  foreach ($arguments as $k => $v) 
    if(strpos($k, '_') === 0 || $k == 'id' || $k == 'updated_at' || $k == 'created_at')
      unset($arguments[$k]);
    else  
      $q .= "$k,";
  $q = substr($q, 0, -1);  // Remove last comma
  $q .= ') VALUES (now(),';
  $q .= str_repeat("?,",count($arguments));
  $q = substr($q, 0, -1);
  $q .= ');';
  $db = dbConnect();
  $stmt = $db->prepare($q);
  bind($stmt, $arguments);
  $stmt->execute();
  //update static id and class id to new inserted value
  return mysqli_insert_id($db);
}

?>