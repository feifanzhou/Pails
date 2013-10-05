<?php 
/** html_helper.php
 ** Functions to generate HTML markup and display code
 **/
include_once('php_helper.php');

function readable_property($name) {
  $s = str_replace('_', ' ', $name);
  $s = ucfirst($s);
  return $s;
}

/**
 * Returns a date string in the requested format
 * @param  int $timestamp    timestamp to format
 * @param  string $format    Format to use
 * Format may be either PHP-native (http://php.net/manual/en/function.date.php)
 * Or a pre-defined format constant (TBD)
 * @return string            Formatted date string
 */
function formattedDate($timestamp, $format) {
  return date($format, $timestamp);
}

function formattedRawDate($mysql_date, $format) {
  $processed_date = sql_date_to_timestamp($mysql_date);
  return formattedDate($processed_date, $format);
}

/**
 * image_tag Returns an img tag from the image provided to the function
 * @param  string   $img_path Can be either a filename ('img.jpg'), or a path
 * @param  Array  $options Parameters for image tag
 * @return string   <img /> tag corresponding to $img_name and $options
 */
function image_tag($img_name, $options=[]) {
  $img_path = $img_name;
  if (!strpos($img_name, 'http') && !strpos($img_name, '/'))  {
  	$img_path = str_replace("/images/","",$img_name);
    // Not path, so create path
    $path_beg = '/app/assets/images/';
    //$img_path = $path_beg . $img_path;
    $img_path = $path_beg . $img_path;
  	if (isset($options['size'])) {
  		$sizes = explode('x', $options['size']);
  		$params = '';
  		$params .= '&w=' . $sizes[0];
  		$params .= '&h=' . $sizes[1];
  		if(isset($options['align'])) //http://www.binarymoon.co.uk/2010/08/timthumb-part-4-moving-crop-location/
  			$params .= '&a=' . $options['align'];
  		$img_path =  '/vendor/timthumb/timthumb.php?src='.$img_path.$params;
  	}
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
	if (isset($options['onclick'])) 
	  $opts .= ('onclick="' . $options['onclick'] . '" ');
	if (isset($options['folder']) && (strpos($img_path,'images/') !== false)){
		$path_exp = explode('images/',$img_path);
		$img_path = $path_exp[0].'images/'. $options['folder'] .'/'.$path_exp[1];
	}
  $img_tag = "<img src='$img_path' alt='$filename' $opts/>";
  return $img_tag;
}

/**
 * image_for Prints an image tag from the image provided to the function
 * @param  string $img_name Can be either a filename ('img.jpg'), or a path
 * @param  Array $options   Parameters for image tag
 * @return nothing
 */
function image_for($img_name, $options=[]) {
  echo image_tag($img_name, $options);
}

function image_link_for($img_name, $link_path='#', $options=[]) {
  echo image_link_tag($img_name, $link_path, $options);
}

function image_link_tag($img_name, $link_path='#', $options=[]){
	$link_text=image_tag($img_name, $options);
	  $opts = '';
  if (isset($options['class']))
    $opts .= ('class="' . $options['class'] . '" ');
  if (isset($options['id']))
    $opts .= ('id="' . $options['id'] . '" ');

  return "<a href='$link_path' $opts>$link_text</a>";
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
 * @param  string $link_text  Text to display for the link
 * @param  string $link_path  Link path
 * @param  [type] $options    Parameters for anchor tag
 * @return nothing
 */
function link_for($link_text, $link_path='#', $options=[]) {
  echo link_tag($link_text, $link_path, $options);
}

/**
 * link_to Returns an anchor tag to a RESTful object
 * By default will return a link to the object's id
 * @param  Object $obj       Object to link to
 * @param  string $link_text Text to display for the link
 * @param  string $disp_col  Displays value from this column in the URL
 * @param  string $slug      Additional slug (SEO-friendly) to display after the main link
 * If slug length is 0, link_text will be used as the slug
 * @param  array  $options   HTML attributes for tag
 * @return string            Anchor tag corresponding to inputs
 */ 
function link_to($obj, $link_text, $disp_col='', $slug='', $options=[]) {
  $cls_name = lcfirst(get_class($obj));
  if (strlen($disp_col) == 0)
    $disp_col = $cls_name . '_id';
  $id = $obj -> $disp_col;
  $path = "/$cls_name/$id";
  if (strlen($slug) == 0) {
    $lt = htmlentities($link_text);
    $lt = str_replace(' ', '%20', $lt);
    $path .= "/$lt";
  }
  // if ($slug == FALSE) {
  //   // Have to strip out the slug, because AND statement above wouldn't work
  //   // Basically trying to hack in an 'unless'
  //   error_log('Slug is false');
  //   $p = explode('/', $path);
  //   array_pop($p);
  //   $path = implode('/', $p);
  //   error_log("Path: $path");
  // }
  if (strlen($slug) > 0) {
    $lt = htmlentities($slug);
    $lt = str_replace(' ', '%20', $lt);
    $path .= "/$slug";
  }
  return link_tag($link_text, $path, $options);
}

/**
 * Creates a button without an associated link
 * @param  string $button_text  Text to display in the button
 * @param  string $button_class Classes for the button
 * @param  string $button_id    Button id
 * @param  array $options       Additional HTML options
 * @return string               HTML button tag
 */
function button_tag($button_text, $button_class='', $button_id='', $options=[]) {
  $opts = '';
  $attrs = '';
  $btn_cls = 'class="btn ' . $button_class . '"';
  $btn_id =  ($button_id) ? 'id="' . $button_id . '" ' : NULL;
  if (is_array($options) && count($options) > 0)
    foreach($options as $k => $v)
      $attrs .= "$k='" . $v . "' ";
  $btn = "<button $btn_cls $btn_id $attrs>$button_text</button>";
  return $btn;
}

function button_for($button_text, $button_class='', $button_id='', $options=[]) {
  echo button_tag($button_text, $button_class, $button_id, $options);
}


/**
 * Creates a button with an associated link
 * @param  string $button_text  Text to display in the button
 * @param  string $link_path    Button links to this URL
 * @param  string $button_class Classes for the button
 * @param  string $button_id    Button id
 * @param  array $options       Additional HTML options
 * @return string               HTML button tag wrapped in a link
 */
function button_to($button_text, $link_path='#', $button_class='', $button_id='', $options=[]) {
  $btn = button_tag($button_text, $button_class, $button_id, $options);
  echo link_tag($btn, $link_path, $options);
}

/**
 * Replaces single newlines with line breaks and double newlines with new paragraphs
 * @param  string $text Input string
 * @return string       A simply formatted string
 */
function simple_format($text) {
  $t = str_replace("\n\n", '</p><p>', $text);
  $t = str_replace("\n", '<br />', $text);
  return $t;
}
?>