<!doctype html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="<?php echo translateWebpageLang(); ?>"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="<?php echo translateWebpageLang(); ?>"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="<?php echo translateWebpageLang(); ?>"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="<?php echo translateWebpageLang(); ?>"> <!--<![endif]-->
<head>
  <meta charset="utf-8">

  <!-- Use the .htaccess and remove these lines to avoid edge case issues.
       More info: h5bp.com/i/378 -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title><?php if (strlen($title) > 0) echo "$title | Sitename"; else echo 'Sitename'; ?></title>
  <meta name="description" content="<?php if (strlen($description) > 0) echo $description; else echo translateHomeMetaDescription(); ?>" />
  <meta property="og:type" content="website"/>
  <meta property="og:title" content="Sitename"/>
  <meta property="og:url" content="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>" />
  <meta property="og:image" content=""/>
  <meta property="og:site_name" content="<?php echo $title ?><?php if (strlen($title) > 0) echo ' | Sitename'; else echo 'Sitename'; ?>"/>
  <meta property="og:description" content="<?php if (strlen($description) > 0) echo $description; else echo translateHomeMetaDescription(); ?>"/>
  <!-- Mobile viewport optimized: h5bp.com/viewport -->
  <meta name="viewport" content="width=device-width">

  <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->
  <link rel="stylesheet" type="text/css" href="/vendor/assets/stylesheets/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="/app/assets/stylesheets/_shared.css">
  <link rel="stylesheet" type="text/css" href="/app/assets/stylesheets/<?php echo $file_name ?>.css">
  <!-- <%= stylesheet_link_tag controller.controller_name %> -->
  <!-- <%= csrf_meta_tags %> -->

  <!-- More ideas for your <head> here: h5bp.com/d/head-Tips -->

  <!-- All JavaScript at the bottom, except this Modernizr build.
       Modernizr enables HTML5 elements & feature detects for optimal performance.
       Create your own custom Modernizr build: www.modernizr.com/download/ -->
  <script src='../../vendor/assets/javascripts/modernizr.min.js' type='text/javascript'></script>

  <!-- Initializes the modernizr and actually perform the checks. Enable this
       if you need the modernizr, but remember to only do the actual checks
       that you need. -->
  <script type='text/javascript'>Modernizr.load();</script>
</head>
<body>
<!--provide div for facebook to hook for login api-->
<div id="fb-root"></div>
  <!-- Prompt IE 6 users to install Chrome Frame. Remove this if you support IE 6.
       chromium.org/developers/how-tos/chrome-frame-getting-started -->
  <!--[if lt IE 7]><p class=chromeframe>Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif]-->
	
  <!-- Add your site or application content here -->
  <!-- Set PHP document root for includes -->
  <?php 
    $paths[] = '.';
    $paths[] = $_SERVER['DOCUMENT_ROOT'] . '/app/controllers';
    $paths[] = $_SERVER['DOCUMENT_ROOT'] . '/app/helpers';
    $paths[] = $_SERVER['DOCUMENT_ROOT'] . '/app/views/layouts';
    $paths[] = $_SERVER['DOCUMENT_ROOT'] . '/app/views/shared';
    $paths[] = $_SERVER['DOCUMENT_ROOT'] . '/config';

    set_include_path(join(PATH_SEPARATOR, $paths));
  ?>

  <?php 
    include_once('php_helper.php');
    include_once('db_helper.php');
    include_once('routes.php');
    include_once('html_helper.php');
    include_once('nocsrf.php');

    function auto_load($class_name) {
      include_once ('../../models/' . lcfirst($class_name) . '.php');
    }
    spl_autoload_register('auto_load');
  ?>