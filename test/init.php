<?php

// Инициализация ядра

namespace is;

// Базовые переменные

$p = __DIR__ . DS;
$file = json_decode(file_get_contents($p . 'settings' . DS . 'init.ini'), true);

?>
<style>body { font-family: monospace; }</style>
<?php

foreach ($file as $item) {
	
	if (
		mb_strpos($item, '!') !== 0 &&
		file_exists($p . 'settings' . DS . $item . '.ini')
	) {
		
		// загружаем файл тестов
		
		$item = json_decode(file_get_contents($p . 'settings' . DS . $item . '.ini'), true);
		
		// подготавливаем тест php
		
		if (!empty($item['php'])) {
			
			$name = '\\is\\Helpers\\' . $item['class'] . '::' . $item['function'];
			
			echo '<p>Function: ' . $name . '<br/>Data: ' . print_r($item['data'], 1) . '</p>';
			
			if (empty($item['cycles'])) {
				$item['cycles'] = 1;
			}
			
			foreach ($item['tests'] as $k => $i) {
				
				$currtime = microtime(true);
				$currmu = memory_get_usage();
				
				for ($n = 1; $n <= $item['cycles']; $n++) {
					
					// выводим заголовок
					
					if (!is_array($i)) {
						if ($n === $item['cycles']) {
							echo '<p>' . $i . '</p>';
						}
						continue;
					}
					
					// запускаем тест
					
					$result = call_user_func_array($name, !empty($item['data']) ? array_merge([$item['data']], $i) : $i);
					
					if ($n === $item['cycles']) {
						if ($item['php'] === 'json') {
							$result = json_encode($result);
						} elseif ($item['php'] === 'dump') {
							$result = var_export($result, 1);
						} elseif ($item['php'] === 'pre') {
							$result = '<pre>' . print_r($result, 1) . '</pre>';
						} else {
							$result = print_r($result, 1);
						}
						echo '<div>#' . $k . ' ' . json_encode($i) . ': |' . $result . '| ' . (microtime(true) - $currtime) . ' sec / ' . (memory_get_usage() - $currmu) . ' byte / ' . $n . ' cycles</pre>';
					}
					
				}
				unset($currtime, $currmu, $currmpu, $n);
				
			}
			unset($k, $i);
			
			echo '<hr>';
			unset($name);
			
		}
		
		// подготавливаем тест js
		
		if (!empty($item['js'])) {
			require $p . 'js.php';
		}
		
		//echo print_r($item, 1);
		
	}
	
}
unset($item);

// запускаем кастомный код

require $p . 'customcode' . DS . 'code.php';
require $p . 'customcode' . DS . 'js.php';

// выводим справочную информацию

echo '<hr>';
echo '<p>END OF TESTS<br>' . number_format(memory_get_usage() / 1024, 3, '.', ' ') . ' KB total / ' . number_format(memory_get_peak_usage() / 1024, 3, '.', ' ') . ' KB in peak</p>';

?>