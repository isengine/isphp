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
	
	$result = [
		__DIR__ . DS,
		realpath(__DIR__ . DS . DP . DP . 'core' . DS . 'model') . DS
	];
	
	foreach ($result as $item) {
		$item .= implode(DS, $array) . '.php';
		if (file_exists($item)) {
			require $item;
		}
		unset($item);
	}
	
});

?>