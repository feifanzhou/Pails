<?php
	include_once('../helpers/db_helper.php');
    include_once('../helpers/php_helper.php');
	$bn = basename($_SERVER['SCRIPT_NAME'], '.php');
	$p = explode('_', $bn);
	$model_name = substr($p[0], 0, -1);
	$model_path = "../models/$model_name.php";
	include_once($model_path);
	include_once('../../config/routes.php');
  $controller_name = $p[0];

  function autoload($class_name) {
    include_once ('../models/' . lcfirst($class_name) . '.php');
  }
  spl_autoload_register('autoload');

  $view_dir = "../views/$model_name";

  // Adapted from Rails RESTful routing
  // http://guides.rubyonrails.org/routing.html#crud-verbs-and-actions
  $current_path = $_SERVER["REQUEST_URI"];
  $p = explode('/', $current_path);
  array_walk($p, function(&$o) {
    // Strip out GET parameters;
    $l = strpos($o, '?');
    if ($l)
      $o = substr($o, 0, $l);
  });
  $current_path = $p[1];
  $action = '';
  $id = -1;
  $type = 'html';
  if (isset($p[2])) {
    $t = explode('.', $p[2]);
    $id = intval($t[0]);
    if (isset($t[1]))
      $type = $t[1];
  }
  if ($id > -1)
    $current_path = $current_path . '/' . $id;
  // Slug is being ignored here
  if (preg_match("/^[a-zA-Z]+s$/", $current_path)) {
    // URL should end in an 's'
    if ($_SERVER['REQUEST_METHOD'] == 'GET')
      $action = 'index';
    else if ($_SERVER['REQUEST_METHOD'] == 'POST')
      $action = 'create';
  }
  else if (preg_match("/^[a-zA-Z]+\/new$/", $current_path)) {
    $action = 'new';
  }
  else if ($id > -1) {
    if ($_SERVER['REQUEST_METHOD'] == 'GET')
      $action = 'show';
    else if ($_SERVER['REQUEST_METHOD'] == 'PUT')
      $action = 'update';
    else if ($_SERVER['REQUEST_METHOD'] == 'DELETE')
      $action = 'destroy';
  }
  else if ($id > -1 && isset($p[3]) && $p[3] == 'edit') {
    if ($_SERVER['REQUEST_METHOD'] == 'GET')
      $action = 'edit';
  }

  if ($type == 'json')
    header('Content-type: application/json');
  
  if (strlen($action) == 0) {
    handle_request($current_path, $_SERVER['REQUEST_METHOD'], array_merge($_GET, $_POST), $type);
  }
  else {
    $params = array_merge($_GET, $_POST);
    $params['action'] = $action;
    $params['type'] = $type;
    if ($id > -1) {
      $params['id'] = $id;
      $action($params);  // Call method by name: http://php.net/manual/en/functions.variable-functions.php
    }
    else
      $action($params);
  }
?>