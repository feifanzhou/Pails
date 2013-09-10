<?php
	include_once('../config/routes.php');
	if ($_SERVER['REQUEST_METHOD'] == 'GET')
		redirect_to('/admin/migrate');

	include_once('../app/helpers/db_helper.php');
	if (isset($_GET['pending']) && $_GET['pending'] == '1') {
		migrate_pending();
		return true;
	}
	else if (isset($_GET['rebuild']) && $_GET['rebuild'] == '1') {
		migrate_rebuild();
		return true;
	}

	if (isset($_POST['table_name'])) {
		$names = columnsForTable($_POST['table_name']);
		echo json_encode($names);
		return true;
	}
	function migrate_pending() {
		$pending = pendingMigrations();
		foreach ($pending as $m) {
			$fn = $m . ".php-db";
			$m_text = file_get_contents($fn);
			$lines = explode("\r\n", $m_text);
			$v = $lines[0];
			$matches;
			$match = preg_match("/\d/", $v, $matches, PREG_OFFSET_CAPTURE);
			if ($match == 0) {
				echo_failure('Migration file');
				return false;
			}
			$v_start = $matches[0][1];
			$tm_s = substr($v, $v_start);
			$tm = intval($tm_s);
			$q = $lines[1];
			$db = dbConnect();
			$result = $db -> query($q);
			if (result === FALSE) {
				echo_failure('Migration query');
				return false;
			}
			update_curr_version($tm);
			update_schema($tm, $q);
		}
		$success = array('success' => 1);
		echo json_encode($success);
		return true;
	}
	function migrate_rebuild() {
		$s_t = file_get_contents('schema.php-db');
		$s_l = explode("\r\n", $s_t);
		for ($i = 1; $i < count($s_l); $i++) {	// Skip first line
			// TODO: Loop through queries and execute them
		}
	}
	// POST to create new migration
	function is_valid($v) {
		if ($v == FALSE)
			return false;
		else {
			return true;
		}
	}
	function echo_failure($invalid_param='') {
		$message = '';
		if (strlen($invalid_param) > 0)
			$message = $invalid_param . ' failed or is invalid';
		$success = array('success' => 0, 'message' => $message);
		echo json_encode($success);
	}
	function update_schema($tm, $query) {
		$file = fopen('schema.php-db', 'c');
		if ($file == FALSE || feof($file)) {
			echo failure('Schema open');
			return false;
		}
		$contents = file_get_contents('schema.php-db');
		$first_line_end = strpos($contents, "\r\n");
		if ($first_line_end === FALSE)
			$first_line_end = 0;
		$rest_of_file = substr($contents, $first_line_end);
		$schema_with_version = "$tm" . $rest_of_file;
		$updated_schema = $schema_with_version . "\r\n" . $query;
		$results = file_put_contents('schema.php-db', $updated_schema);
		if (!is_valid($results)) {
			echo_failure('Schema write');
			return false;
		}
		return true;
	}
	function create_migration_file($migration, $query, $tm) {
		$tm = time();
		$mgt = "version=$tm".PHP_EOL;
		$mgt .= $query;
		$mgt .= PHP_EOL;
		$mgt .= json_encode($migration);
		$name_rep = str_replace(' ', '_', $migration['name']);
		$fn = "$tm" . "_$name_rep";
		$fnx = $fn  . ".php-db";
		$fp = "migrations/$fnx";
		$results = file_put_contents($fp, $mgt);
		if (!is_valid($results)) {
			echo_failure('Migration write');
			return false;
		}
		// Update schema
		update_schema($tm, $query);
		return $fn;
	}
	function update_curr_version($tm) {
		$results = file_put_contents('.current_version', $tm);
		if ($results == FALSE) {
			echo_failure('Write current version');
			return false;
		}
		return true;
	}

	$tm = time();
	$migration = $_POST['migration'];
	if (!is_valid($migration)) {
		echo_failure('Form input');
		return false;
	}
	$table_name = $migration['table'];
	if (!is_valid($table_name)) {
		echo_failure('Table name');
		return false;
	}
	$action = $migration['action'];
	if (!is_valid($action)) {
		echo_failure('Action');
		return false;
	}
	$col_name = $migration['column'];
	if (!is_valid($col_name)) {
		echo_failure('Column name');
		return false;
	}
	$c_t = '';
	$after_col = '';
	if ($action == 'ADD') {
		$col_type = $migration['col_type'];
		if (!is_valid($col_type)) {
			$col_type = '';
			echo_failure('Column type');
			return false;
		}
		$col_param = $migration['col_param'];
		if (!is_valid($col_param)) {
			$col_param = '';
			echo_failure('Column details');
			return false;
		}
		$c_t = $col_type . '(' . $col_param . ')';
		$after_col = $migration['after_col'];
		if (is_valid($after_col))
			$after_col = 'AFTER ' . $after_col;

		// $n = 'NOT NULL';
		// if (isset($migration['is_null']) && $migration['is_null'] == 'NULL')
		// 	$n = 'NULL';
	}
	// $qs = "ALTER TABLE $table_name $action $col_name $c_t $after_col $n";
	$qs = "ALTER TABLE $table_name $action $col_name $c_t $after_col";
	$db = dbConnect();
	$result = $db -> query($qs);
	error_log("===== qs: $qs");
	if ($result === FALSE) {
		echo_failure('SQL Query');
		return false;
	}
	$fn = create_migration_file($migration, $qs, $tm);
	update_curr_version($tm);

	$success = array('success' => 1, 'migration_name' => $fn);
	echo json_encode($success);
?>