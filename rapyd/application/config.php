<?php

/**
 * system configurations
 *
 */

$config['index_page'] = 'index.php'; //use 'index.php' if htaccess not allowed
$config['basename'] = ''; //it correspont to .htaccess BASENAME
$config['url_method'] = 'uri'; //alternative: "qs"  define if rapyd will use uri or query string for its semantic
$config['default_controller'] = 'welcome';
$config['default_method']     = 'index';


$config['include_paths'][]  = 'application';
$config['include_paths'][]  = 'modules/demo';


$config['root_path']  = getenv('DOCUMENT_ROOT'); // or './../../';

$config['include_paths'][]  = 'core';

$config['assets_path']     = RAPYD_PATH.'core/assets/';
$config['cache_path']      = RAPYD_ROOT.'cache/';
//$config['locale_language'] = 'en_US';
$config['locale_language'] = 'es_ES';

$config['routes'] = array(
	//'product/(:num)/:str' => 'catalogmodule/product/$1';
);

include(RAPYD_ROOT.'../application/config/database.php');

$config['db']['hostname'] = $db['default']['hostname'];
$config['db']['username'] = $db['default']['username'];
$config['db']['password'] = $db['default']['password'];
$config['db']['database'] = $db['default']['database'];
$config['db']['dbdriver'] = $db['default']['dbdriver'];
$config['db']['dbprefix'] = $db['default']['dbprefix'];
$config['db']['db_debug'] = $db['default']['db_debug'];
/**
 * custom configurations
 *
 */