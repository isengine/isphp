<?php

namespace is\Masters\Drivers\Master;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Local;
use is\Helpers\Parser;

class Cache extends Common
{
    public $cache; // кэшированные данных
    public $cached; // триггер, что запрос прокеширован
    public $hash; // хэш, контрольная сумма запроса, по которой запрос будет искаться в кэше

    public function cache($path)
    {
        if (!file_exists($path)) {
            Local::createFolder($path);
        }
        $this->cache = $path;
    }

    public function readCache()
    {
        $path = $this->cache . $this->collection . DS . $this->hash . '.ini';
        if (file_exists($path)) {
            $this->cached = true;
            //$file = Local::readFile($path);
            //return Parser::fromJson($file);
            foreach (Local::readFileGenerator($path) as $line) {
                $parse = Parser::fromJson($line);
                if ($parse) {
                    $this->addData($parse);
                }
            }
        }
    }

    public function writeCache()
    {
        $file = $this->cache . $this->collection . DS . $this->hash . '.ini';
        Local::createFile($file);
        //$data = Local::writeFileGenerator($file);
        foreach ($this->data as $item) {
            $parse = Parser::toJson($item);
            Local::writeFile($file, $parse . "\n", 'append'); //
            //$data->send($parse);
        }
        unset($item, $parse);
        //$data->send(null);
        //unset($data);
    }

    public function hash()
    {
        $json = json_encode($this->filter) . json_encode($this->fields) . json_encode($this->rights);
        $this->hash = md5($json) . '.'
            . Strings::len($json) . '.'
            . (int) $this->settings['all'] . '.'
            . $this->settings['limit'];
    }
}
