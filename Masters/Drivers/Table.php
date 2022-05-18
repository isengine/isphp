<?php

namespace is\Masters\Drivers;

use is\Helpers\Sessions;
use is\Helpers\Parser;
use is\Helpers\Objects;
use is\Helpers\Strings;
use is\Helpers\Local;
use is\Helpers\System;
use is\Helpers\Matches;

class Table extends Master
{
    protected $path;
    protected $parent;

    public function connect()
    {
        $this->path = preg_replace('/[\\/]+/ui', DS, DR . str_replace(':', DS, $this->settings['name']) . DS);
        $this->parent = Objects::convert($this->settings['parents']);
    }

    public function close()
    {
    }

    public function hash()
    {
        $json = json_encode($this->filter) . json_encode($this->fields) . json_encode($this->rights);
        $path = $this->path . $this->collection;
        $this->hash = (Local::matchFile($path) ? md5_file($path) : 0) . '.'
            . md5($json) . '.'
            . Strings::len($json) . '.'
            . (int) $this->settings['all'] . '.'
            . (int) $this->settings['limit'];
    }

    public function read()
    {
        $path = $this->path . $this->collection;

        if (!Local::matchFile($path)) {
            return;
        }

        $stat = stat($path);

        if ($handle = fopen($path, "r")) {
            //$excel = SimpleXLSX::parse($path);

            //return [
            //    'parents' => Objects::convert(str_replace(DS, ':', Strings::unlast($item['path']))),
            //    'id' => $parse['id'],
            //    'name' => str_replace('--', '.', $parse['name']),
            //    'type' => Objects::convert(str_replace(['--', ' '], ['.', ':'], $parse['type'])),
            //    'owner' => Objects::convert(str_replace(['--', ' '], ['.', ':'], $parse['owner'])),
            //    'ctime' => $stat['ctime'],
            //    'mtime' => $stat['mtime'],
            //    'dtime' => $parse['dtime'],
            //];

            // Общие настройки

            $delimiter = $this->settings['delimiter'] ? $this->settings['delimiter'] : ',';
            $enclosure = $this->settings['enclosure'] ? $this->settings['enclosure'] : '"';

            $rowkeys = $this->settings['rowkeys'] ? $this->settings['rowkeys'] : 0;

            $rowskip =
                $this->settings['rowskip']
                ? (
                    is_array($this->settings['rowskip'])
                    ? $this->settings['rowskip']
                    : Objects::convert($this->settings['rowskip'])
                )
                : [];

            if (System::typeOf($rowkeys, 'iterable')) {
                $keys = $rowkeys;
            } else {
                $index = 0;
                while ($row = fgetcsv($handle, null, $delimiter, $enclosure)) {
                    if ($index === $rowkeys) {
                        $keys = $row;
                        break;
                    }
                    $index++;
                }
            }

            // Построчная обработка

            $index = 0;

            $count = 0;

            rewind($handle);

            while ($row = fgetcsv($handle, null, $delimiter, $enclosure)) {
                if (Matches::equalIn($rowskip, $index)) {
                    $index++;
                    continue;
                }

                $entry = Objects::join($keys, $row);
                //$entry = Objects::combine($row, $keys);

                // создание новых полей/колонок и обработка текущих
                $this->fields($entry);

                // проверка по имени
                if (!$this->verifyName($entry['name'])) {
                    $entry = null;
                }

                if ($entry) {
                    foreach ($entry as $k => $i) {
                        /*
                        // Это условие надо убрать, иначе будут биться любые строки
                        // Нужно оставить разбор, как он был задан - через настройки контента
                        // КСТАТИ, ЭТИ НАСТРОЙКИ ТАКЖЕ МОЖНО ВНЕСТИ В НАСТРОЙКИ ДРАЙВЕРА
                        // И ТОГДА БУДЕТ ОЧЕНЬ КРУТО !!!
                        // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
                        // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
                        // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
                        // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
                        // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
                        // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
                        if (
                            Strings::match($i, ':')
                            || Strings::match($i, '|')
                        ) {
                            $i = Parser::fromString($i);
                        }
                        */
                        if ($this->settings['encoding']) {
                            $i = mb_convert_encoding($i, 'UTF-8', $this->settings['encoding']);
                        }
                        if (Strings::match($k, ':')) {
                            // А вот это условие оставить - т.к. бьются только ключи и это правильно
                            $levels = Parser::fromString($k);
                            $entry = Objects::add($entry, Objects::inject([], $levels, $i), true);
                            unset($entry[$k], $levels);
                        } elseif (Objects::match(['type', 'parents', 'owner'], $k) && System::typeOf($i, 'scalar')) {
                            // Это условие тоже нужно оставить для базовых полей
                            if (
                                Strings::match($i, ':')
                                || Strings::match($i, '|')
                            ) {
                                $entry[$k] = Parser::fromString($i);
                            }
                        }
                    }
                    unset($k, $i);

                    // несколько обязательных полей
                    //if (!$entry['parents']) {
                    //    $entry['parents'] = $this->parent;
                    //}
                    if (System::typeIterable($this->parent)) {
                        $entry['parents'] = Objects::add($this->parent, $entry['parents']);
                    }
                    if (!$entry['ctime']) {
                        $entry['ctime'] = $stat['ctime'];
                    }
                    if (!$entry['mtime']) {
                        $entry['mtime'] = $stat['mtime'];
                    }

                    // проверка по датам
                    if (!$this->verifyTime($entry)) {
                        $entry = null;
                    }
                }

                // контрольная проверка
                $entry = $this->verify($entry);

                $count = $this->result($entry, $count);
                if (!System::set($count)) {
                    break;
                }

                $index++;
            }
        }
    }
}
