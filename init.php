<?php

// Инициализация ядра

namespace is;

// Базовые константы

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('DP') or define('DP', '..' . DIRECTORY_SEPARATOR);

// определяем функции инициализации компонентов системы

require_once __DIR__ . DS . 'php' . DS . 'init.php';
require_once __DIR__ . DS . 'js' . DS . 'init.php';

// запускаем тестировщик

require_once __DIR__ . DS . 'test' . DS . 'init.php';

?>