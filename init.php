<?php

// Базовые константы

if (!defined('isENGINE')) { define('isENGINE', microtime(true)); }
if (!defined('DS')) { define('DS', DIRECTORY_SEPARATOR); }
if (!defined('DP')) { define('DP', '..' . DIRECTORY_SEPARATOR); }
if (!defined('DR')) { define('isROOT', realpath(__DIR__ . DS . DP . DP . DP) . DS); }

// определяем функции инициализации компонентов системы

require_once __DIR__ . DS . 'php' . DS . 'init.php';
require_once __DIR__ . DS . 'js' . DS . 'init.php';

// запускаем тестировщик

require_once __DIR__ . DS . 'test' . DS . 'init.php';

?>