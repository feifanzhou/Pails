<?php 
/** html_helper.php
 ** Functions to generate HTML markup
 **/

/**
 * image_tag Returns an img tag from the image provided to the function
 * @param  string 	$img_path Can be either a filename ('img.jpg'), or a path
 * @param  Array 	$options Parameters for image tag
 * @return string 	<img /> tag corresponding to $img_name and $options
 */
function image_tag($img_name, $options=[]) {
	$img_path = $img_name;
	if (!strpos($img_name, 'http') && !strpos($img_name, '/'))	{
		// Not path, so create path
		$path_beg = '../app/assets/images/';
		$img_path = $path_beg . $img_path;
	}
	$exp = explode('/', $img_path);
	$path_comp = array_pop($exp);
	$file_pieces = explode('.', $path_comp);
	$filename = $file_pieces[0];

	// Process options
	$opts = '';
	if (isset($options['class']))
		$opts .= ('class="' . $options['class'] . '" ');
	if (isset($options['id']))
		$opts .= ('id="' . $options['id'] . '" ');
	if (isset($options['size'])) {
		$sizes = explode('x', $options['size']);
		$opts .= ('width="' . $sizes[0] . 'px" ');
		$opts .= ('height="' . $sizes[1] . 'px" ');
	}
	if (isset($options['width']))
		$opts .= ('width="' . $options['width'] . '" ');
	if (isset($options['height']))
		$opts .= ('height="' . $options['height'] . '" ');
	$img_tag = "<img src='$img_path' alt='$filename' $opts/>";
	return $img_tag;
}

/**
 * image_for Prints an image tag from the image provided to the function
 * @param  string $img_name Can be either a filename ('img.jpg'), or a path
 * @param  Array $options 	Parameters for image tag
 * @return nothing
 */
function image_for($img_name, $options=[]) {
	echo image_tag($img_name, $options);
}

function link_tag($link_text, $link_path='#', $options=[]) {
	$opts = '';
	if (isset($options['class']))
		$opts .= ('class="' . $options['class'] . '" ');
	if (isset($options['id']))
		$opts .= ('id="' . $options['id'] . '" ');

	return "<a href='$link_path' $opts>$link_text</a>";
}

/**
 * link_for Prints an anchor tag
 * @param  string $link_text	Text to display for the link
 * @param  string $link_path	Link path
 * @param  [type] $options 		Parameters for anchor tag
 * @return nothing
 */
function link_for($link_text, $link_path='#', $options=[]) {
	echo link_tag($link_text, $link_path, $options);
}

function button_for($button_text, $link_path='#', $button_class='', $button_id='', $options=[]) {
	$opts = '';
	$btn_cls = 'class="btn ' . $button_class . '"';
	$btn_id = 'id="' . $button_id . '" ';
	if (isset($options['class']))
		$opts .= ('class="' . $options['class'] . '" ');
	if (isset($options['id']))
		$opts .= ('id="' . $options['id'] . '" ');
	$btn = "<button $btn_cls $btn_id>$button_text</button>";
	echo link_tag($btn, $link_path, $options);
}
?>