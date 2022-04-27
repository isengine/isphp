<?php

// Базовые переменные

$host = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/';
$agent = $_SERVER['HTTP_USER_AGENT'];
$count = count($item['query']);

// функция

if (!function_exists('is_curl_test')) {
function is_curl_test($url, $agent)
    {
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
}

// код шаблна

echo '<style>
.inside {
    display: inline-block;
    vertical-align: top;
    /* float: left; */
    width: calc(' . floor(100 / $count) . '% - 20px);
    padding: 0 10px;
}
.clear {
    float: none;
    clear: both;
}
</style>';

echo '<div>';

foreach ($item['query'] as $i) {
    echo '<div class="inside">' . $i['title'] . '</div>';
}
unset($i);

echo '</div>';
echo '<div class="clear"></div>';
echo '<div>';

foreach ($item['query'] as $i) {
    echo '<div class="inside">';
    foreach ($item['url'] as $part) {
        echo '<hr>TEST URL: [' . htmlentities($part) . ']';
        $url = $host . $part . (mb_strpos($part, '?') !== false ? '&' : '?');
        foreach ($i['data'] as $dk => $di) {
            $url .= $dk . '=' . $di . '&';
        }
        unset($dk, $di);
        is_curl_test($url, $agent);
    }
    echo '</div>';
}

echo '</div>';
echo '<div class="clear"></div>';
