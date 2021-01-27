<?php

// Инициализация ядра

namespace is;
use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Data;

$a = '0123456789abcdef';
$a = '0123456789';
$b = 'abcdef';

for ($i = 0, $n = 1000000; $i < $n; $i++) {

$a = 'a123:123:123|b123::';
$a = ['a123','123','123','b123','123'];
$a = ['a' => ['11','12','13'], 'b' => ['11','22','33'], 'c' => ['123']];
$a = ['11','22','33','44','55','66'];
$a = ['e' => 10, 'b' => 20, 'c' => 30, 'd' => 40];
$a = ["1e" => 10, "2b" => 20, "3c" => 30, "4d" => 40];
$a = ["1" => 10, "2" => 20, "3" => 30, "4" => 40];
$a = 'a123';
$a = (object) [1, 2, 4];
$r = Data::to($a);

}

echo '<pre>(' . var_dump($r) . ')</pre>';
echo '<pre>' . print_r(Data::associate($a), 1) . '</pre>';
echo '<pre>' . print_r(Data::len($a), 1) . '</pre>';
echo '<pre>' . print_r(Data::first($a), 1) . '</pre>';
echo '<pre>' . print_r(Data::last($a), 1) . '</pre>';
echo '<pre>' . print_r(Data::reverse($a), 1) . '</pre>';

?>