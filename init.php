<?php

// Рабочее пространство имен

namespace is;

// Базовые константы

if (!defined('isENGINE')) { define('isENGINE', microtime(true)); }
if (!defined('DS')) { define('DS', DIRECTORY_SEPARATOR); }
if (!defined('DP')) { define('DP', '..' . DIRECTORY_SEPARATOR); }
if (!defined('DI')) { define('DI', realpath($_SERVER['DOCUMENT_ROOT']) . DS); }
if (!defined('DR')) { define('DR', realpath(__DIR__ . DS . DP . DP . DP) . DS); }

// Подключение классов

spl_autoload_register(function($class) {
	
	$array = explode('\\', $class);
	array_shift($array);
	
	$path = __DIR__ . DS . implode(DS, $array) . '.php';
	if (file_exists($path)) {
		require $path;
	}
	unset($path);
	
});

?>