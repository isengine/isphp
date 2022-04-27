<?php

namespace is;

use is\Helpers\System;
use is\Helpers\Objects;
use is\Helpers\Prepare;

use is\Parents\Collection;
use is\Parents\Catalog;

use is\Globals\Uri;
use is\Globals\Session;
use is\Constants\Config;

//use is\Managers\Uri as UriManager;

$str = 'sadfsd';
$tags = '123:123:123|123:123';
$tags = '123:123:123';
//$tags = '["123", "123", "123"]';
//$tags = '{"a":"123", "b":"123", "c":"123"}';
Prepare::stripTags($str, $tags);

//$url = new Uri();
//$url->get();
//$url->init();
//$url->state();
//$url->set();

//$urls = Uri::getInstance();
//$urls->init();
//echo '<pre>' . print_r($urls, 1) . '</pre>';

//$config = Config::getInstance();
//$config->init();
//$config->data['core']['composer'] = 123;
//$config->set('DEFAULT_TIMEZONE', 123);
//echo '<pre>' . print_r($config->set('DEFAULT_PROCESSOR', 123), 1) . '</pre>';
//echo '<pre>' . print_r($config->set('default:processorz', 123), 1) . '</pre>';
//echo '<pre>' . print_r($config->set('default_processors', false), 1) . '</pre>';
//echo '<pre>' . print_r($config->set('default:processors', true), 1) . '</pre>';
//echo '<pre>' . print_r($config->get(), 1) . '</pre>';
//echo '<pre>' . print_r($config->get('DEFAULT_PROCESSOR'), 1) . '</pre>';
//echo '<pre>' . print_r($config->is('default:processorss'), 1) . '</pre>';
//echo '<pre>' . print_r($config, 1) . '</pre>';

$data = [
    [
        'name' => 'b',
        'ctime' => 222,
        'data' => [
            'title' => 'b'
        ]
    ],
    [
        'name' => 'a',
        'ctime' => 111,
        'data' => [
            'title' => 'a'
        ]
    ],
    [
        'name' => 'c',
        'ctime' => 333,
        'data' => [
            'title' => 'c'
        ]
    ]
];

$col = new Collection($data);
echo '<pre>' . print_r($col, 1) . '</pre>';

//$cat = clone $col;
//$cat->count++;
//echo '<pre>' . print_r($cat, 1) . '</pre>';

$cat = new Catalog($col);
echo '<pre>' . print_r($cat, 1) . '</pre>';

//$col->remove(1);
//$cat->init();
//$cat->sortById();
$cat->sortByEntry('ctime');
$cat->sortByData('title');
//$cat->sortByName();
//$cat->reverse();
$cat->randomize();
echo '<pre>' . print_r($col->get('names'), 1) . '</pre>';
echo '<pre>' . print_r($cat->get('names'), 1) . '</pre>';
