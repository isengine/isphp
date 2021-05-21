<?php

// Рабочее пространство имен

namespace is;

// Базовые константы

if (!defined('isENGINE')) { define('isENGINE', microtime(true)); }
if (!defined('DS')) { define('DS', DIRECTORY_SEPARATOR); }
if (!defined('DP')) { define('DP', '..' . DIRECTORY_SEPARATOR); }
if (!defined('DR')) { define('DR', realpath(__DIR__ . DS . DP . DP . DP) . DS); }

// Подключение классов

spl_autoload_register(function($class) {
	
	$array = explode('\\', $class);
	array_shift($array);
	
	//$file = mb_strtolower(array_pop($array)) . '.php';
	//$folder = __DIR__ . DS . mb_strtolower(implode(DS, $array));
	//$result = str_replace('\\', DS, $folder . DS . $file);
	
	$result = str_replace('\\', DS, __DIR__ . DS . implode(DS, $array) . DS . '.php');
	
	if (file_exists($result)) {
		require $result;
	}
	
});

?>