<?php

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
