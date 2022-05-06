<?php

namespace is\Masters\Drivers;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Parser;
use is\Helpers\Local;

class Localdb extends Master
{
    protected $path;
    public $matched;

    public function connect()
    {
        $this->path = preg_replace('/[\\/]+/ui', DS, DR . str_replace(':', DS, $this->settings['name']) . DS);
    }

    public function close()
    {
    }

    public function hash()
    {
        $json = json_encode($this->filter) . json_encode($this->fields) . json_encode($this->rights);
        $path = $this->path . $this->collection;
        $this->hash =
            (Local::matchFile($path) ? md5_file($path) : 0) . '.' .
            md5($json) . '.' .
            Strings::len($json) . '.' .
            (int) $this->settings['all'] . '.' .
            (int) $this->settings['limit'];
    }

    public function read()
    {
        $path = $this->path . $this->collection . DS;

        $files = [];

        $files = Local::search(
            $path,
            ['return' => 'files', 'extension' => 'ini', 'subfolders' => true, 'merge' => true]
        );
        //echo '<pre>' . print_r($files, 1) . '</pre>';

        $count = 0;

        foreach ($files as $key => $item) {
            $entry = $this->createInfoFromFile($item, $key);

            // создание новых полей/колонок и обработка текущих
            $this->fields($entry);

            // проверка по имени
            if (!$this->verifyName($entry['name'])) {
                $entry = null;
            }

            // проверка по датам
            if (!$this->verifyTime($entry)) {
                $entry = null;
            }

            if ($entry) {
                $entry['data'] = $this->readDataFromFile($entry['path']);
            }

            // контрольная проверка
            $entry = $this->verify($entry);

            $count = $this->result($entry, $count);
            if (!System::set($count)) {
                break;
            }
        }
        unset($key, $item);

        unset($files);
    }

    public function write()
    {
        if (!$this->data['data']) {
            return;
        }

        $item = $this->createInfoForFile($this->data);

        // в matched мы сохраняем состояния проверки
        // проверка идет на наличие записей, поэтому
        // в данном случае там находится путь к файлу

        // объединяем данные

        $data = Parser::fromJson(Local::readFile($this->matched));
        if ($data) {
            $item['data'] = Objects::merge($data, $item['data'], true);
        }

        // затем записываем туда данные в формате json

        $result = $this->writeDataToFile(
            $this->matched,
            $item['data']
        );

        return $result;
    }

    public function create()
    {
        $item = $this->createInfoForFile($this->data);

        // смотрим, есть ли нужная директория и если нет, то создаем ее

        if (!Local::matchFolder($item['path'])) {
            Local::createFolder($item['path']);
        }

        return $this->writeDataToFile(
            $item['path'] . $item['file'],
            $item['data']
        );
    }

    public function delete()
    {
        // в matched мы сохраняем состояния проверки
        // проверка идет на наличие записей, поэтому
        // в данном случае там находится путь к файлу

        return Local::deleteFile($this->matched);
    }

    public function match()
    {
        // нам нужна проверка существования записи
        // тогда и только тогда мы сможем вынести эту проверку за пределы
        // методов create, write и т.д.
        // и сможем поместить ее в мастер-класс, родительский
        // чтобы переходить к непосредственно выполнению заданий
        // только после проверок
        // тогда не нужно будет реализовывать эти проверки в каждом задании,
        // и можно будет сосредоточиться на правильном выполнении самого задания

        // сбрасываем прежние результаты

        $this->matched = null;

        // делаем обработку данных

        $item = $this->createInfoForFile($this->data);

        // ищем, есть ли хотя бы один подходящий файл в заданном пути

        $files = Local::search(
            $item['path'],
            ['return' => 'files', 'extension' => 'ini', 'subfolders' => null, 'merge' => true]
        );

        // проверяем, найдены ли вообще файлы

        if (!$files || !is_array($files)) {
            return;
        }

        // перебираем все файлы и ищем те, которые подходят под нашу запись

        foreach ($files as $i) {
            $id = Strings::match($i['name'], '.' . $item['name'] . '.');
            $noid = Strings::find($i['name'], $item['name'] . '.', 0);

            if ($id || $noid) {
                // это совпадение, значит файл существует
                // записываем это состояние
                // возвращаем его данные
                $this->matched = $i['fullpath'];
                return true;
            }
        }

        return;
    }

    private function readDataFromFile($path)
    {
        return Parser::fromJson(
            Local::readFile($path),
            $this->format ? $this->format : true
        );
    }

    private function writeDataToFile($path, $data)
    {
        return Local::writeFile(
            $path,
            Parser::toJson(
                $data,
                $this->format ? $this->format : true
            ),
            'replace'
        );
    }

    private function createInfoFromFile($item, $key)
    {
        $stat = stat($item['fullpath']);

        // здесь мы распарсиваем имя на составляющие по точкам,
        // затем выясняем, есть ли здесь идентификатор
        // и сводим все в массив стандартной записи в базе данных

        //echo print_r($item, 1) . '<br>';

        $parse = Strings::split($item['file'], '\.');

        $first = Objects::first($parse, 'value');
        //$second = Objects::n($parse, 1, 'value');
        $second = Objects::first(Objects::get($parse, 1, 1), 'value');

        if (
            !is_numeric($first) ||
            is_numeric($first) && !$second
        ) {
            $parse = Objects::add([$key], $parse);
        }

        $parse = Objects::join(['id', 'name', 'type', 'owner', 'dtime'], $parse);

        return [
            'path' => $item['fullpath'],
            'parents' => Objects::convert(Strings::replace(Strings::unlast($item['path']), DS, ':')),
            'id' => $parse['id'],
            'name' => Strings::replace($parse['name'], '--', '.'),
            'type' => Objects::convert(Strings::replace($parse['type'], ['--', ' '], ['.', ':'])),
            'owner' => Objects::convert(Strings::replace($parse['owner'], ['--', ' '], ['.', ':'])),
            'ctime' => $stat['ctime'],
            'mtime' => $stat['mtime'],
            'dtime' => $parse['dtime'],
        ];
    }

    private function createInfoForFile($item)
    {
        // сначала создаем правильное содержимое записи
        // переводим нужные поля в пути

        $path =
            $this->path . $this->collection . DS .
            ($item['parents'] ? Strings::join($item['parents'], DS) . DS : null);

        $item['name'] = Strings::replace($item['name'], '.', '--');
        $item['type'] = Strings::replace(Strings::join($item['type'], ' '), '.', '--');
        $item['owner'] = Strings::replace(Strings::join($item['owner'], ' '), '.', '--');

        $file = $item['name'] . '.';
        if (System::set($item['type'])) {
            $file .= $item['type'] . '.';
        }
        if (System::set($item['owner'])) {
            if (
                !System::set($item['type'])
            ) {
                $file .= '.';
            }
            $file .= $item['owner'] . '.';
        }
        if ($item['dtime'] && System::type($item['dtime'], 'numeric')) {
            if (
                !System::set($item['type']) &&
                !System::set($item['owner'])
            ) {
                $file .= '..';
            } elseif (
                !System::set($item['owner'])
            ) {
                $file .= '.';
            }
            $file .= $item['dtime'] . '.';
        }
        $file .= 'ini';

        return [
            'path' => $path,
            'file' => $file,
            'id' => $item['id'],
            'name' => $item['name'],
            'data' => $item['data']
        ];
    }
}
