<?php

// Инициализация ядра

namespace is;

// Базовые переменные

$path = __DIR__ . DS;
$file = json_decode(file_get_contents($path . 'settings' . DS . 'init.ini'), true);

?>
<style>body { font-family: monospace; }</style>
<?php

foreach ($file as $key => $item) {
    $t = $path . 'settings' . DS . $item . DS . $key . '.ini';

    if (
        mb_strpos($key, '!') !== 0
        && file_exists($t)
    ) {
        // загружаем файл тестов
        $item = json_decode(file_get_contents($t), true);
        unset($t);
        $template = $item['template'] ? $item['template'] : 'default';
        require $path . 'templates' . DS . $template . '.php';
    }
}
unset($item);

// запускаем кастомный код

//require $path . 'customcode' . DS . 'code.php';
//require $path . 'customcode' . DS . 'js.php';

// выводим справочную информацию

echo '<hr>';
echo '<p>END OF TESTS<br>' . number_format(memory_get_usage() / 1024, 3, '.', ' ') . ' KB total / ' . number_format(memory_get_peak_usage() / 1024, 3, '.', ' ') . ' KB in peak</p>';

exit;
