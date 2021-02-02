<?php

// Базовые константы

require_once __DIR__ . DIRECTORY_SEPARATOR . 'defines.php';

// определяем функции инициализации компонентов системы

require_once __DIR__ . DS . 'php' . DS . 'init.php';
require_once __DIR__ . DS . 'js' . DS . 'init.php';

// запускаем тестировщик

require_once __DIR__ . DS . 'test' . DS . 'init.php';

?>