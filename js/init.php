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

function jsSearch($path, &$time) {
	
	if (!file_exists($path) || !is_dir($path)) {
		return false;
	}
	
	$path = str_replace(['/', '\\'], DS, $path);
	if (substr($path, -1) !== DS) { $path .= DS; }
	
	$scan = scandir($path);
	
	if (!is_array($scan)) {
		return false;
	}
	
	$list = [];
	$dir = [];
	
	foreach ($scan as $item) {
		if ($item === '.' || $item === '..') {
			continue;
		}
		$item = $path . $item;
		if (is_dir($item)) {
			$dir[] = $item;
			//$sub = \is\jsSearch($item . DS, $time);
			//if (is_array($sub)) {
			//	$list = array_merge($list, $sub);
			//}
		} else {
			if (mb_substr($item, -3) === '.js') {
				$list[] = $item;
				$mtime = filemtime($item);
				if ($time < $mtime) {
					$time = $mtime;
				}
			}
		}
	}
	unset($item);
	
	foreach ($dir as $item) {
		$sub = \is\jsSearch($item . DS, $time);
		if (is_array($sub)) {
			$list = array_merge($list, $sub);
		}
	}
	unset($item);
	
	return $list;
	
}

$time = null;
$path = __DIR__ . DS;
$list = \is\jsSearch($path, $time);

$file = 'is.js';
$mtime = file_exists(DI . $file) ? filemtime(DI . $file) : null;

if ($mtime <= $time) {
	$content = null;
	foreach ($list as $item) {
		$content .= file_get_contents($item) . "\n";
	}
	unset($item);
	file_put_contents(DI . $file, $content);
	$mtime = filemtime(DI . $file);
}

?>
<script src="<?= '//' . $_SERVER['HTTP_HOST'] . '/' . $file . '?' . $mtime; ?>"></script>