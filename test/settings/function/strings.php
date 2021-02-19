<?php

// Инициализация ядра

namespace is;
use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Data;

//$string = 'a123:123:123|b123:123';

//for ($i = 0, $n = 1000; $i < $n; $i++) {
//$string = 'a123:!123:-123.50|!b123:123|c123:123::1';
//$p = Data::parse($string, ['key' => true, 'clear' => true, 'simple' => true]);
//}
//echo '<pre>';
//System::print($p);
//var_dump($p);
//echo '</pre>';

//$array = ['123', '123', '123'];
$array = range(1, 100000, 1);

foreach ($array as &$item) {
	$item .= '+';
}
unset($item);

//$r = System::foreach($array, null, function($i) {
//	return $i .= '+';
//});

//$array = [10,20,30,40];
//$r = System::foreach($array, [], function($i, $k, &$c) {
//	$c[$k] = $i++;
//});

//echo '<pre>' . print_r($r, 1) . '</pre>';

//$a = '0123456789abcdef';
//$a = '0123456789';
//$b = 'abcdef';
//for ($i = 0, $n = 1000000; $i < $n; $i++) {
//$a = 'a123:123:123|b123::';
//$a = ['a123','123','123','b123','123'];
//$a = ['a' => ['11','12','13'], 'b' => ['11','22','33'], 'c' => ['123']];
//$a = ['11','22','33','44','55','66'];
//$a = ['e' => 10, 'b' => 20, 'c' => 30, 'd' => 40];
//$a = ["1e" => 10, "2b" => 20, "3c" => 30, "4d" => 40];
//$a = ["1" => 10, "2" => 20, "3" => 30, "4" => 40];

//$a = 'a123';
//$a = (object) [1, 2, 4];
//$r = Data::to($a);
//$r = Strings::find($a, 'a123', 0);
//$r = Strings::get($a, 6);
//$r = Strings::first($a);
//$r = Strings::last($a);
//$r = Strings::replace($a, '0', '#');
//$r = Strings::add($a, $b, true);
//$a = 'А роза упала на лапу Азора ウィキ';
//$r = Strings::replace($a, 'а', 'ウ');
//$r = Strings::replace($a, ['А', 'а'], ['ан', '#']);
//$r = Strings::len($a);
//}
//echo '<pre>(' . var_dump($r) . ')</pre>';

?>