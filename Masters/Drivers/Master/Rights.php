<?php

namespace is\Masters\Drivers\Master;

use is\Helpers\System;
use is\Helpers\Strings;
use is\Helpers\Objects;

class Rights extends Cache
{
    public $rights; // права доступа к базе данных
    public $rights_query; // права текущего запроса
    public $owner; // имя текущего владельца

    public function rights($rights, $owner = null)
    {
        $this->rights = $rights;
        $this->owner = $owner;
    }

    public function setRights()
    {
        $rights = null;
        if (
            isset($this->rights[$this->collection][$this->query]) &&
            (
                $this->rights[$this->collection][$this->query] ||
                $this->rights[$this->collection][$this->query] === false
            )
        ) {
            $rights = $this->rights[$this->collection][$this->query];
        } elseif (
            isset($this->rights[$this->collection]) &&
            (
                $this->rights[$this->collection] ||
                $this->rights[$this->collection] === false
            )
        ) {
            $rights = $this->rights[$this->collection];
        } elseif (
            isset($this->rights[$this->query]) &&
            (
                $this->rights[$this->query] ||
                $this->rights[$this->query] === false
            )
        ) {
            $rights = $this->rights[$this->query];
        } else {
            $rights = $this->rights;
        }
        return $rights;
    }

    public function entryRights($entry)
    {
        if (!$entry || !$this->rights_query) {
            return null;
        }

        //$rights = $this->setRights();
        // чтобы не делать права для каждой записи,
        // мы делаем права для запроса
        $rights = Objects::createByIndex(
            ['filters', 'method', 'owner', 'allow', 'deny'],
            $this->rights_query
        );

        $owner = null;
        if (isset($entry['owner'])) {
            $owner = $entry['owner'] && System::typeOf($entry['owner'], 'iterable') ? Objects::match($entry['owner'], $this->owner) : $entry['owner'] === $this->owner;
        }

        // теперь нет allow и deny как раньше - правила строятся на основе фильтров
        // allow и deny которые теперь, используются для сравнения ключей массива
        // перечисленные в allow ключи остаются
        // перечисленные в deny ключи убираются
        // owner теперь - true/false
        // owner также разрешает доступ к записям, если совпадает владелец (имя)
        // и он также в приоритете

        if ($rights['owner'] && $this->owner && $owner) {
            // если это условие убрать, то приоритеты проверки сломаются
        } elseif (is_array($rights)) {
            if (is_array($rights['filters'])) {
                // сохраняем настройки и значения фильтров

                $filters = $this->filter;

                // задаем новые

                $this->filter->resetFilter();
                if ($rights['method']) {
                    $this->filter->methodFilter($rights['method']);
                }
                unset($rights['method']);

                // здесь делаем добавление правил из $rights в фильтры

                foreach ($rights['filters'] as $key => $item) {
                    if (!is_array($item)) {
                        $this->filter->addFilter($key, $item);
                    }
                }
                unset($key, $item);

                // здесь делаем проверку по фильтру

                if (!$this->filter->filtration($entry)) {
                    $entry = null;
                }

                // возвращаем сохраненные настройки и значения фильтров

                $this->filter = $filters;
            }

            // здесь очищаем поля deny
            // либо оставляем только поля allow
            // это касается только ключей полей данных записи, ключи исключения передаются в массиве

            $allow = System::typeIterable($rights['allow']);
            $deny = System::typeIterable($rights['deny']);
            $data = isset($entry['data']) && System::typeIterable($entry['data']);

            if ($deny && $data) {
                $entry['data'] = Objects::removeByIndex($entry['data'], $rights['deny']);
            }
            if ($allow && $data) {
                $keys = Objects::keys($entry['data']);
                $keys = Objects::remove($keys, $rights['allow']);
                $entry['data'] = Objects::removeByIndex($entry['data'], $keys);
                unset($keys);
            }

            unset($allow, $deny, $data);
        } elseif (!$rights) {
            $entry = null;
        }

        //echo '[' . $this->collection . ':' . print_r($entry, 1) . ']<br><br>';
        return $entry;
    }
}
