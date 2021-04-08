<?php

// Рабочее пространство имен

namespace is;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Paths;
use is\Model\Components\Config;
use is\Model\Components\Display;
use is\Model\Components\Log;
use is\Model\Components\Router;
use is\Model\Components\Language;
use is\Model\Templates\Template;

// читаем конфиг

$config = Config::getInstance();
$router = Router::getInstance();

// инициализируем шаблонизатор с параметрами

$template = Template::getInstance();
$template -> init([
	//'view' => $config -> get('default:view'),
	'path' => $config -> get('path:templates') . $router -> template['name'] . DS,
	'cache' => $config -> get('path:cache') . 'templates' . DS,
	'render' => [
		'from' => [
			$config -> get('path:templates') . $router -> template['name'] . DS,
			DS
		],
		'url' => [
			'/' . Paths::clearSlashes($config -> get('url:assets')) . '/',
			'/' . $router -> template['name'] . '/'
		],
		'to' => [
			$config -> get('path:site') . Paths::toReal(Paths::clearSlashes($config -> get('url:assets'))) . DS,
			DS . Paths::toReal($router -> template['name']) . DS
		]
	]
]);

// задаем кэширование блоков
// и запрещаем кэширование страниц
//$template -> view -> setCachePages(false);
//$template -> view -> setCacheBlocks(true);
// НЕ ЗАБЫТЬ УБРАТЬ КОММЕНТАРИИ !!!!!!!!

// запускаем обнаружение устройств
//$template -> view -> detect -> init();

// пример рендеринга css файла
//$result = $template -> render('css', 'filename');
//echo $result;

//$print = Display::getInstance();
//$print -> dump($user -> getData());
//echo '<hr>';
//$print -> dump($db);
//$print -> dump($uri);
//$print -> dump($state);
//$print -> dump($template);
//$print -> dump($router);
//
//exit;

?>