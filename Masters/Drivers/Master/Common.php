<?php

namespace is\Masters\Drivers\Master;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;
use is\Helpers\Prepare;
use is\Parents\Data;
use is\Components\Filter;

class Common extends Data
{
    public $settings; // настройки подключения

    public $query; // тип запроса в базу данных - чтение, запись, добавление, удаление
    public $collection; // раздел базы данных

    public $filter; // параметры фильтрации результата из базы данных
    public $fields; // параметры правил обработки полей
    public $format; // формат данных в json (это может быть контент или структура)

    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->filter = new Filter();
    }

    public function collection($name)
    {
        $this->collection = $name;
    }

    public function query($name)
    {
        $this->query = $name;
    }

    public function settings($key, $item)
    {
        $this->settings[$key] = $item;
    }

    public function field($key, $item)
    {
        $this->settings['fields'][$key] = $item;
    }

    public function format($item)
    {
        $this->format = $item;
    }

    public function result($entry, $count)
    {
        if ($entry) {
            $this->addData($entry);
            $count++;
            if (
                isset($this->settings['limit']) &&
                $this->settings['limit'] &&
                $this->settings['limit'] <= $count
            ) {
                $count = null;
            }
        }
        return $count;
    }

    public function fields(&$entry, $fill = null)
    {
        // создание новых колонок и обработка текущих
        // .
        // правила задаются в разделе настроек 'fields'
        // в виде ассоциированного массива
        // ключ обозначает название колонки
        // внутри могут содержаться параметры:
        // from - имя колонки, откуда берется значение
        // default - значение по-умолчанию, если значение ячейки оказалось пустым
        // prepare - настройки обработки
        // .
        // имя колонки, откуда берется значение,
        // может быть одной из стандартных (is, name, parents...),
        // либо из массива data (data:title, data:custom...)
        // специальное значение 'fill' позволяет брать значение
        // из переданного в метод аругмента fill
        // если по итогу from и default значение оказалось было пустым
        // .
        // да, много чего нет, например преобразование даты/времени из формата unix
        // но это, очевидно, и не нужно, т.к. его будет разумнее сделать во view
        // во-первых, это может зависеть от языковых настроек,
        // которые здесь не поддерживаются и не должны по правилам
        // распределения обязанностей фреймворка и хелперов в частности,
        // а во-вторых, для этого есть отдельные решения, например текстовые переменные
        // .
        // примеры:
        // "fields" : {
        //   "name" : {
        //     "from" : "data:title",
        //     "prepare" : "trim:spaces",
        //     "prepare" : "trim|spaces:_"
        //   },
        //   "parents" : {
        //     "from" : "data:group",
        //     "prepare" : "len:2:3|trim",
        //     "prepare" : [
        //       ["len", 2, 3],
        //       ["trim"]
        //     ]
        //   },
        //   "data:another" : {
        //     "from" : "fill",
        //     "prepare" : "toObject"
        //   },
        //   "data:tags" : {
        //     "from" : "parents",
        //     "default" : "value",
        //     "prepare" : "toObject"
        //   }
        // }

        if (
            isset($this->settings['fields']) &&
            System::typeIterable($this->settings['fields'])
        ) {
            foreach ($this->settings['fields'] as $k => $i) {
                
                $i = Objects::merge(
                    [
                        'from' => null,
                        'prepare' => null,
                        'default' => null
                    ],
                    $i
                );
                
                $in_entry = isset($entry[$k]) && System::set($entry[$k]);
                
                if ($i['from'] === 'fill') {
                    $col = $in_entry ? $entry[$k] : $fill;
                } else {
                    if ($in_entry) {
                        $col = $entry[$k];
                    } else {
                        $val = $i['from'] ? $i['from'] : $k;
                        $col = isset($entry[$val]) ? $entry[$val] : null;
                    }
                    //$col = isset($entry[$k]) && System::set($entry[$k]) ? $entry[$k] : $entry[ $i['from'] ? $i['from'] : $k ];
                }

                if (System::set($col) && $i['prepare']) {
                    $prepare = Objects::convert($i['prepare']);
                    foreach ($prepare as $pi) {
                        if (is_array($pi)) {
                            $pn = 'is\Helpers\Prepare::' . Objects::first($pi, 'value');
                            Objects::refirst($pi, $col);
                            $col = call_user_func_array($pn, $pi);
                        } else {
                            $col = Prepare::$pi($col);
                        }
                    }
                    unset($pi);
                }

                if (
                    !System::set($col) &&
                    $i['default']
                ) {
                    $col = $i['default'];
                }

                //System::debug($col, '!q');
                $entry[$k] = $col;
                unset($col);
            }
            unset($k, $i);
        }
    }
}
