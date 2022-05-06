<?php

namespace is;

spl_autoload_register(function ($class) {
    $array = explode('\\', $class);
    array_shift($array);

    $path = __DIR__ . DS . implode(DS, $array) . '.php';
    if (file_exists($path)) {
        require $path;
    }
    unset($path);
});
