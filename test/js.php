<script>
<?php

// задаем данные

$namejs = "is.Helpers." . $item['class'] . "." . $item['function'];
$datajs = !empty($item['data']) ? json_encode($item['data']) : "\"\"";
echo "var data = " . $datajs . ";\r\n";

echo "console.log('Function: " . $namejs . "');\r\n";
echo "console.log('Data: " . $datajs . "');\r\n";

foreach ($item['tests'] as $k => $i) {
    // выводим заголовок

    if (!is_array($i)) {
        echo "console.log('" . $i . "');\r\n";
        continue;
    }

    // подготавливаем настройки теста

    $s = $namejs . "(" . (!empty($item['data']) ? "data" : null);

    if (!empty($i)) {
        foreach ($i as $ki => $ii) {
            $s .= empty($item['data']) && !$ki ? null : ', ';
            if (is_bool($ii)) {
                $s .= $ii ? 'true' : 'false';
            } elseif (is_array($ii)) {
                $s .= json_encode($ii);
            } elseif (!$ii && $ii !== 0 && $ii !== '0') {
                $s .= 'null';
            } elseif (is_numeric($ii)) {
                $s .= $ii;
            } else {
                $s .= "'" . $ii . "'";
            }
        }
        unset($ki, $ii);
    }

    $s .= ")";
    if ($item['js'] === 'json') {
        $s = "JSON.stringify(" . $s . ")";
    }

    // запускаем тест

    echo "console.log('#" . $k . ' ' . json_encode($i) . ":', " . $s . ");\r\n";
}
unset($k, $i);
echo "console.log('');\r\n";

?>
</script>