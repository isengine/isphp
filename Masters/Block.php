<?php

namespace is\Masters;

use is\Helpers\Sessions;
use is\Helpers\Parser;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Local;
use is\Helpers\System;
use is\Helpers\Matches;
use is\Helpers\Paths;
use is\Helpers\Prepare;
use is\Parents\Data;
use is\Components\Cache;

class Block extends Data
{
    // общие установки кэша

    public $path; // путь до папки блоков, массив, строится из частей
    // /templates/...template-name.../html/blocks/...block-name...
    public $cache_path; // путь до кэша
    public $caching; // разрешение кэширования по-умолчанию
    public $template; // шаблон по-умолчанию
    public $custom; // идентификаторы пользователя, браузера и языка для разных кэшей

    public function init($path, $template, $custom, $cache_path, $caching = false)
    {
        $this->path = $path;
        $this->template = $template;
        $this->custom = $custom;
        $this->cache_path = $cache_path;
        $this->caching = $caching;
    }

    // $path = $config->get('path:templates');
    // $cache = $config->get('path:cache') . 'templates' . DS;
    // $view->get('layout')->init('blocks', $path, $cache); // переключить на true
    // запуск
    // $view->get('module')->launch('data', 'eshop-toasts');
    // $view->get('layout')->launch('blocks', 'header|top-nav');
    // $view->get('block')->launch('block-name-with-path', 'template-if-need');

    public function launch($name, $template = null, $caching = 'default')
    {
        if (!$template) {
            $template = $this->template;
        }

        $path = Paths::toReal($this->path[0] . $template . DS . $this->path[1] . $name . '.php');
        $cache_path = Paths::toReal($this->cache_path . $template . DS . $this->path[1]);

        // сюда же можно добавить кэш

        $cache = new Cache($cache_path);
        $cache->caching($caching === 'default' ? $this->caching : $caching);
        $cache->init($name, $template, $this->custom);
        $cache->compare($path);

        //$cache->self = $path;

        $data = $cache->start();

        if (!$data && file_exists($path)) {
            // запуск блока
            require $path;
            $return = true;
        } else {
            $return = false;
        }

        //System::debug($cache);

        $cache->stop();

        return $return;
    }
}
