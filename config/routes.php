<?php 
function redirect_to($path) {
	$loc = "Location: $path";
	header($loc);
}

function root_path() {
	return '/';
}

function welcome_path() {
	return '/welcome';
}

?>