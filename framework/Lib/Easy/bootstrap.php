<?php

/**
 *  Carregamento das funcionalidades básicas do EasyFramework.
 *
 *  @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 *  @copyright Copyright 2011, EasyFramework (http://www.easy.lellysinformatica.com)
 *
 */
/* Path to the temporary files directory. */
if (!defined('TMP')) {
    define('TMP', APP_PATH . 'tmp' . DS);
}
/* Path to the cache files directory. It can be shared between hosts in a multi-server setup. */
if (!defined('CACHE')) {
    define('CACHE', TMP . 'cache' . DS);
}
/* Path to the log files directory. It can be shared between hosts in a multi-server setup. */
if (!defined('LOGS')) {
    define('LOGS', TMP . 'logs' . DS);
}

if (!defined('EASY_CORE_INCLUDE_PATH')) {
    define('EASY_CORE_INCLUDE_PATH', dirname(__FILE__));
}

if (!defined('CORE')) {
    define('CORE', EASY_CORE_INCLUDE_PATH . DS);
}

/* Basic classes */
require CORE . 'basics.php';
require CORE . 'Common' . DS . 'App.php';
require CORE . 'Error' . DS . 'Exceptions.php';

/* Register the autoload function for the Lazy load */
spl_autoload_register(array('App', 'load'));

/* Build the App configs */
App::build();

App::uses('Object', 'Common');
App::uses('Mapper', 'Dispatcher');
App::uses('I18n', 'Localization');

App::uses('Error', 'Error');
App::uses('Config', 'Common');
App::uses('Cache', 'Cache');
App::uses('Debugger', 'Utility');

App::uses('Inflector', 'Common');
App::uses('Security', 'Security');

App::uses('AppController', 'Controller');
App::uses('AppModel', 'Model');

Config::bootstrap();