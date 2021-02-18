<?php

// Инициализация ядра

namespace is;

// Базовые переменные

$p = __DIR__ . DS;
$file = json_decode(file_get_contents($p . 'querysettings' . DS . 'init.ini'), true);

$sn = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/';
$agent = $_SERVER['HTTP_USER_AGENT'];

echo '<div>';

//echo '<div style="float: left; width: 33%">1.1.2. Папки преобразуются в файлы php, с индексными</div>';
//echo '<div style="float: left; width: 33%">1.2.1. Папки преобразуются в файлы html, без индексных, а php запрещены</div>';
//echo '<div style="float: left; width: 33%">1.3.1. Папки преобразуются в файлы html, без индексных, а php и htm также конвертируются в html</div>';

echo '<div style="float: left; width: 33%">2.1.3. В папки преобразуются только файлы php, только индексные</div>';
echo '<div style="float: left; width: 33%">2.2.2. В папки преобразуются только файлы html, без учета индексных</div>';
echo '<div style="float: left; width: 33%">2.3.1. В папки преобразуются файлы html (а php и htm также конвертируются в html), с учетом индексных</div>';

echo '</div>';

echo '<div style="float: none; clear: both;"></div>';

echo '<div>';
foreach ($file as $part) {
echo '<div style="float: left; width: 33%">';
	
foreach ($part as $item) {
	echo '<hr>';
	//echo 'NEW QUERY<br>';
	echo 'TEST URL: [' . htmlentities($item) . ']';
	is_curl_test($sn . $item, $agent);
}

echo '</div>';
}
echo '</div>';
echo '<div style="float: none; clear: both;"></div>';

function is_curl_test($url, $agent) {
	
	// curl
	
	$result = [];
	
	if (!extension_loaded('curl')) {
		echo 'NOT!';
		exit;
	}
	
	$init = curl_init();
	
	curl_setopt($init, CURLOPT_URL, $url);
	curl_setopt($init, CURLOPT_HTTPGET, true);
	curl_setopt($init, CURLOPT_USERAGENT, $agent);
	curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
	
	$result['exec'] = curl_exec($init);
	$result['info'] = curl_getinfo($init);
	
	curl_close($init);
	
	// end curl
	
	//echo '<hr>';
	echo '<br>';
	echo print_r($result['exec'], 1);
	//echo '<hr>';
	//echo 'INFO:<pre>' . print_r($result['info'], 1) . '</pre>';
	
}

echo '<hr>';
echo '<p>END OF TESTS<br>' . number_format(memory_get_usage() / 1024, 3, '.', ' ') . ' KB total / ' . number_format(memory_get_peak_usage() / 1024, 3, '.', ' ') . ' KB in peak</p>';

?>