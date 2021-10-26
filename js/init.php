<?php

// Рабочее пространство имен

namespace is;

// Базовые константы

if (!defined('isENGINE')) { define('isENGINE', microtime(true)); }
if (!defined('DS')) { define('DS', DIRECTORY_SEPARATOR); }
if (!defined('DP')) { define('DP', '..' . DIRECTORY_SEPARATOR); }
if (!defined('DI')) { define('DI', realpath($_SERVER['DOCUMENT_ROOT']) . DS); }
if (!defined('DR')) { define('DR', realpath(__DIR__ . DS . DP . DP . DP . DP) . DS); }

// Подключение элементов

function isJsSearch($path) {
	
	if (!file_exists($path) || !is_dir($path)) {
		return false;
	}
	
	$path = str_replace(['/', '\\'], DS, $path);
	if (substr($path, -1) !== DS) { $path .= DS; }
	
	$scan = scandir($path);
	
	if (!is_array($scan)) {
		return false;
	}
	
	//$scan = Objects::sort($scan);
	
	$list = [];
	
	foreach ($scan as $item) {
		if ($item === '.' || $item === '..') {
			continue;
		}
		$item = $path . $item;
		if (is_dir($item)) {
			$sub = isJsSearch($item . DS);
			if (is_array($sub)) {
				$list = array_merge($list, $sub);
			}
		} else {
			$info = pathinfo($item);
			if (is_array($info) && $info['extension'] === 'js') {
				$list[] = [$item, filemtime($item)];
			}
		}
	}
	
	return $list;
	
}

$path = __DIR__ . DS;
$list = isJsSearch($path);
rsort($list);

$file = 'is.' . md5(json_encode($list)) . '.js';

if (!file_exists(DI . $file)) {
	$content = null;
	foreach ($list as $item) {
		$content .= file_get_contents($item[0]) . "\n";
	}
	unset($item);
	file_put_contents(DI . $file, $content);
}

//echo '<pre>' . print_r($file, 1) . '</pre>';
//echo '<pre>' . print_r($list, 1) . '</pre>';

?>

<script src="<?= '//' . $_SERVER['HTTP_HOST'] . '/' . $file; ?>"></script>
