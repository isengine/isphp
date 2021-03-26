<?php

// Инициализация ядра

namespace is;
use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Data;

// Базовые константы

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('DP') or define('DP', '..' . DIRECTORY_SEPARATOR);

$time = microtime(true);
$mu = memory_get_usage();
$mpu = memory_get_peak_usage();
$path = __DIR__ . DS;

// определяем функции инициализации компонентов системы

require_once $path . 'helpers' . DS . 'init.php';

/*
//$string = 'a123:123:123|b123:123';

//for ($i = 0, $n = 1000; $i < $n; $i++) {
//$string = 'a123:!123:-123.50|!b123:123|c123:123::1';
//$p = Data::parse($string, ['key' => true, 'clear' => true, 'simple' => true]);
//}
//echo '<pre>';
//System::render($p);
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
*/

$a = 'positionare';
//echo '<pre>REVERSE</pre>';
//echo '<pre>' . print_r(Strings::cut($a, 1, 0, true), 1) . '</pre>'; // p(ositionare)
//echo '<pre>' . print_r(Strings::cut($a, 1, 1, true), 1) . '</pre>'; // p(ositionar)e
//echo '<pre>' . print_r(Strings::cut($a, 1, 2, true), 1) . '</pre>'; // p(ositiona)re
//echo '<pre>DEF</pre>';
//echo '<pre>' . print_r(Strings::cut($a, 1), 1) . '</pre>'; // p
//echo '<pre>' . print_r(Strings::cut($a, 3), 1) . '</pre>'; // pos
//echo '<pre>' . print_r(Strings::cut($a, 6), 1) . '</pre>'; // positi
//echo '<pre>' . print_r(Strings::cut($a, -1), 1) . '</pre>'; // positionar
//echo '<pre>' . print_r(Strings::cut($a, -3), 1) . '</pre>'; // position
//echo '<pre>' . print_r(Strings::cut($a, -6), 1) . '</pre>'; // posit
//echo '<pre>CUT</pre>';
//echo '<pre>' . print_r(Strings::cut($a, 0), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::cut($a, 3), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::cut($a, 6), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::cut($a, 0, 3), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::cut($a, 3, 3), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::cut($a, 6, 3), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::cut($a, 6, -3), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::cut($a, -1), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::cut($a, -3), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::cut($a, -6), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::cut($a, -6, 3), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::cut($a, -6, -3), 1) . '</pre>';
//echo '<pre>---</pre>';
//echo '<pre>+' . print_r(Strings::cut($a, 6, 30), 1) . '</pre>';
//echo '<pre>+' . print_r(Strings::cut($a, 6, -30), 1) . '</pre>';
//echo '<pre>+' . print_r(Strings::cut($a, -6, 30), 1) . '</pre>';
//echo '<pre>+' . print_r(Strings::cut($a, -6, -30), 1) . '</pre>';
//echo '<pre>+' . print_r(Strings::cut($a, 30, 30), 1) . '</pre>';
//echo '<pre>+' . print_r(Strings::cut($a, 30, -30), 1) . '</pre>';
//echo '<pre>+' . print_r(Strings::cut($a, -30, 30), 1) . '</pre>';
//echo '<pre>+' . print_r(Strings::cut($a, -30, -30), 1) . '</pre>';
//echo '<pre>STRING</pre>';
//echo '<pre>' . print_r(Strings::get($a, 0), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::get($a, 3), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::get($a, 6), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::get($a, 0, 3), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::get($a, 3, 3), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::get($a, 6, 3), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::get($a, 6, -3), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::get($a, -3), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::get($a, -6), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::get($a, -6, 3), 1) . '</pre>';
//echo '<pre>' . print_r(Strings::get($a, -6, -3), 1) . '</pre>';
//echo '<pre>---</pre>';
//echo '<pre>r ' . print_r(Strings::get($a, 0, 0, 'r'), 1) . '</pre>';
//echo '<pre>r ' . print_r(Strings::get($a, 1, 1, 'r'), 1) . '</pre>';
//echo '<pre>r ' . print_r(Strings::get($a, 2, 2, 'r'), 1) . '</pre>';
//echo '<pre>---</pre>';
//echo '<pre>+' . print_r(Strings::get($a, 6, 30), 1) . '</pre>';
//echo '<pre>+' . print_r(Strings::get($a, 6, -30), 1) . '</pre>';
//echo '<pre>+' . print_r(Strings::get($a, -6, 30), 1) . '</pre>';
//echo '<pre>+' . print_r(Strings::get($a, -6, -30), 1) . '</pre>';
//echo '<pre>+' . print_r(Strings::get($a, 30, 30), 1) . '</pre>';
//echo '<pre>+' . print_r(Strings::get($a, 30, -30), 1) . '</pre>';
//echo '<pre>+' . print_r(Strings::get($a, -30, 30), 1) . '</pre>';
//echo '<pre>+' . print_r(Strings::get($a, -30, -30), 1) . '</pre>';
$a = ['p','o','s','i','t','i','o','n','a','r','e'];
//$a = ['p1'=>1,'o2'=>2,'s3'=>3,'i4'=>4,'t5'=>5,'i6'=>6,'o7'=>7,'n8'=>8,'a9'=>9,'r10'=>10,'e11'=>11];

echo '<pre>REVERSE</pre>';
echo '<pre>' . print_r(Data::cut($a, 1, 0, true), 1) . '</pre>'; // p(ositionare)
echo '<pre>' . print_r(Data::cut($a, 1, 1, true), 1) . '</pre>'; // p(ositionar)e
echo '<pre>' . print_r(Data::cut($a, 1, 2, true), 1) . '</pre>'; // p(ositiona)re
echo '<pre>DEF</pre>';
echo '<pre>' . print_r(Data::cut($a, 1), 1) . '</pre>'; // p
echo '<pre>' . print_r(Data::cut($a, 3), 1) . '</pre>'; // pos
echo '<pre>' . print_r(Data::cut($a, 6), 1) . '</pre>'; // positi
echo '<pre>' . print_r(Data::cut($a, -1), 1) . '</pre>'; // positionar
echo '<pre>' . print_r(Data::cut($a, -3), 1) . '</pre>'; // position
echo '<pre>' . print_r(Data::cut($a, -6), 1) . '</pre>'; // posit
echo '<pre>CUT</pre>';
echo '<pre>' . print_r(Data::cut($a, 0, 1), 1) . '</pre>';
echo '<pre>' . print_r(Data::cut($a, 3, 1), 1) . '</pre>';
echo '<pre>' . print_r(Data::cut($a, 6, 1), 1) . '</pre>';
echo '<pre>' . print_r(Data::cut($a, 0, 3), 1) . '</pre>';
echo '<pre>' . print_r(Data::cut($a, 3, 3), 1) . '</pre>';
echo '<pre>' . print_r(Data::cut($a, 6, 3), 1) . '</pre>';
echo '<pre>' . print_r(Data::cut($a, 6, -3), 1) . '</pre>';
echo '<pre>' . print_r(Data::cut($a, -1, 1), 1) . '</pre>';
echo '<pre>' . print_r(Data::cut($a, -3, 1), 1) . '</pre>';
echo '<pre>' . print_r(Data::cut($a, -6, 1), 1) . '</pre>';
echo '<pre>' . print_r(Data::cut($a, -6, 3), 1) . '</pre>';
echo '<pre>' . print_r(Data::cut($a, -6, -3), 1) . '</pre>';
echo '<pre>---</pre>';
echo '<pre>+' . print_r(Data::cut($a, 6, 30), 1) . '</pre>';
echo '<pre>+' . print_r(Data::cut($a, 6, -30), 1) . '</pre>';
echo '<pre>+' . print_r(Data::cut($a, -6, 30), 1) . '</pre>';
echo '<pre>+' . print_r(Data::cut($a, -6, -30), 1) . '</pre>';
echo '<pre>+' . print_r(Data::cut($a, 30, 30), 1) . '</pre>';
echo '<pre>+' . print_r(Data::cut($a, 30, -30), 1) . '</pre>';
echo '<pre>+' . print_r(Data::cut($a, -30, 30), 1) . '</pre>';
echo '<pre>+' . print_r(Data::cut($a, -30, -30), 1) . '</pre>';


//echo '<pre>DATA</pre>';
//echo '<pre>' . print_r(Data::get($a, 0), 1) . '</pre>';
//echo '<pre>' . print_r(Data::get($a, 3), 1) . '</pre>';
//echo '<pre>' . print_r(Data::get($a, 6), 1) . '</pre>';
//echo '<pre>' . print_r(Data::get($a, 0, 3), 1) . '</pre>';
//echo '<pre>' . print_r(Data::get($a, 3, 3), 1) . '</pre>';
//echo '<pre>' . print_r(Data::get($a, 6, 3), 1) . '</pre>';
//echo '<pre>' . print_r(Data::get($a, 6, -3), 1) . '</pre>';
//echo '<pre>' . print_r(Data::get($a, -3), 1) . '</pre>';
//echo '<pre>' . print_r(Data::get($a, -6), 1) . '</pre>';
//echo '<pre>' . print_r(Data::get($a, -6, 3), 1) . '</pre>';
//echo '<pre>' . print_r(Data::get($a, -6, -3), 1) . '</pre>';
//echo '<pre>---</pre>';
//echo '<pre>r ' . print_r(Data::get($a, 0, 0, 'r'), 1) . '</pre>';
//echo '<pre>r ' . print_r(Data::get($a, 1, 1, 'r'), 1) . '</pre>';
//echo '<pre>r ' . print_r(Data::get($a, 2, 2, 'r'), 1) . '</pre>';
//echo '<pre>---</pre>';
//echo '<pre>+' . print_r(Data::get($a, 6, 30), 1) . '</pre>';
//echo '<pre>+' . print_r(Data::get($a, 6, -30), 1) . '</pre>';
//echo '<pre>+' . print_r(Data::get($a, -6, 30), 1) . '</pre>';
//echo '<pre>+' . print_r(Data::get($a, -6, -30), 1) . '</pre>';
//echo '<pre>+' . print_r(Data::get($a, 30, 30), 1) . '</pre>';
//echo '<pre>+' . print_r(Data::get($a, 30, -30), 1) . '</pre>';
//echo '<pre>+' . print_r(Data::get($a, -30, 30), 1) . '</pre>';
//echo '<pre>+' . print_r(Data::get($a, -30, -30), 1) . '</pre>';

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
//echo '<pre>' . print_r(Data::associate($a), 1) . '</pre>';
//echo '<pre>' . print_r(Data::len($a), 1) . '</pre>';
//echo '<pre>' . print_r(Data::first($a), 1) . '</pre>';
//echo '<pre>' . print_r(Data::last($a), 1) . '</pre>';
//echo '<pre>' . print_r(Data::reverse($a), 1) . '</pre>';

echo '<br>' . (memory_get_usage() - $mu) . ' / ' . (memory_get_peak_usage() - $mpu) . '<br>' . (microtime(true) - $time);

require_once $path . 'js' . DS . 'init.php';

?>
