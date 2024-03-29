<?php

namespace is\Helpers;

class System
{
    /**
     * Подключение файлов
     * once влияет не на первое включение, а только на повторные
     *
     * @param [type] $item
     * @param [type] $base
     * @param boolean $once
     * @param [type] $object
     * @param [type] $return
     * @return void
     */
    public static function includes($item, $base = __DIR__ . DS . DP, $once = true, $object = null, $return = null)
    {
        $item = str_replace(['..','.','\/','\\',':'], ['','',DS,DS,DS], $item);
        $path = realpath($base . DS . $item . '.php');

        // здесь realpath был расширен на весь путь, а не только на base,
        // так как он возвращает false, когда base не существует
        // и путь получается некорректным

        if ($path && file_exists($path)) {
            if ($once) {
                require_once $path;
            } else {
                require $path;
            }
            if ($return) {
                return $$return;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Проверка существования переменной
     * главное отличие в том, что проверка на ноль дает true
     * проверка на пустой массив дает false
     *
     * @param [type] $item
     * @return void
     */
    public static function set($item = null)
    {
        if (
            isset($item)
            && $item === true
        ) {
            return true;
        } elseif (
            !isset($item)
            //|| $item === 'false'
            //|| $item === 'null'
            || $item === false
            || $item === null
        ) {
            return null;
        } elseif (
            empty($item)
            && is_numeric($item)
        ) {
            return true;
        } elseif (empty($item)) {
            return null;
        } elseif (
            is_array($item)
            || is_object($item)
        ) {
            foreach ($item as $i) {
                if (self::set($i)) {
                    return true;
                }
            }
            return null;
        } elseif (
            is_string($item)
            && (
                mb_strpos($item, ' ') !== false
                || mb_strpos($item, '    ') !== false
            )
        ) {
            return preg_replace('/[\s]+/ui', '', $item) ? true : null;
        }

        return true;
    }

    /**
     * Проверка значения с его возвратом
     * специальные аргументы
     * before и after - если проверка прошла успешно, значение возвращается с заданными строками перед и после него
     * not - данное значение возвращается в случае если проверка не прошла успешно
     *
     * @param [type] $item
     * @param [type] $before
     * @param [type] $after
     * @param [type] $not
     * @return void
     */
    public static function setReturn($item = null, $before = null, $after = null, $not = null)
    {
        return self::set($item) ? $before . $item . $after : $not;
    }

    /**
     * Возвращает тип или проверку типа переменной
     * Число, в том числе строка записанная числом
     * Объект/именованный не пустой массив
     * Простой, неименованный не пустой массив
     * Строка
     * Триггер/булев
     * Пустой элемент, false, null, undefined, пустая строка или пустой объект/массив, но не ноль
     * Вторым аргументом добавлена проверка на указанный тип
     *
     * @param [type] $item
     * @param [type] $compare
     * @return void
     */
    public static function type($item = null, $compare = null)
    {
        $type = null;
        $data = null;
        $set = self::set($item);

        if (is_array($item)) {
            $type = 'array';
        } elseif (is_object($item)) {
            $type = 'object';
        } elseif (!$set) {
            $type = null;
        } elseif (is_bool($item)) {
            $type = 'true';
        } elseif (is_string($item)) {
            $item = preg_replace('/\s/', '', $item);
            $item = str_replace(',', '.', $item);
            $set = self::set($item);

            if (!$set) {
                $type = null;
            } elseif (is_numeric($item)) {
                $type = 'numeric';
            } else {
                $type = 'string';
            }
        } elseif (is_numeric($item)) {
            $type = 'numeric';
        } else {
            $type = 'string';
        }

        if ($compare) {
            return $compare === $type ? true : null;
        }

        return $type;
    }

    /**
     * Более упрощенная проверка на принадлежность к типу:
     *     скаляный (строковый) тип - строка или число
     *     итерируемый (объектный) тип - массив или объект
     * Второй аргумент позволяет задать сравнение с типом и вывести его результат
     *
     * Призвана заменить многократные проверки
     * type && ( type === string || type === numeric )
     * type && ( type === array || type === object )
     * данная функция возвращает тип, даже если содержимое пустое
     *
     * @param [type] $item
     * @param [type] $compare
     * @return void
     */
    public static function typeOf($item = null, $compare = null)
    {
        $type = null;

        //if (is_scalar($item) && !is_bool($item)) {
        if (is_scalar($item) && $item !== false) {
            // УСЛОВИЕ ПРОВЕРКИ ДОПОЛНИЛОСЬ НА ОТМЕНУ BOOLEAN
            $type = 'scalar';
        } elseif (is_array($item) || is_object($item)) {
            $type = 'iterable';
        } else {
            $set = self::set($item);
            if (!$set || $item === true || is_resource($item)) {
                return null;
            }
            unset($set);
        }

        if ($compare) {
            return $compare === $type ? true : null;
        }

        return $type;
    }

    /**
     * Проверка на принадлежность к системному типу данных:
     *     скаляный (строковый) тип - строка или число
     *     итерируемый (объектный) тип - массив или объект
     *     json данные
     *     системные данные
     * Второй аргумент позволяет задать сравнение с типом и вывести его результат
     *
     * данная функция не возвращает тип, если содержимое пустое,
     * т.к. любой тип с пустым содержимым не относится к системным данным
     *
     * Призвана заменить
     * проверку type по данным
     * проверку objects::is
     * и расширить проверку на json
     *
     * @param [type] $item
     * @param [type] $compare
     * @return void
     */
    public static function typeData($item = null, $compare = null)
    {
        // Внимание! Здесь намеренное различие типов с версией для js
        // Objects::is($item) => System::typeData(item, 'object')

        $type = self::type($item);
        $result = null;

        if ($type === 'string') {
            $first = $item[0];
            $last = mb_substr($item, -1);
            if (
                ($first === '{' && $last === '}')
                || ($first === '[' && $last === ']')
            ) {
                $result = 'json';
            } elseif (mb_strpos($item, ':') !== false || mb_strpos($item, '|') !== false) {
                $result = 'string';
            }
            unset($first, $last);
        } elseif ($type === 'array') {
            $result = 'object';
        }

        if ($compare) {
            return $compare === $result ? true : null;
        }

        return $result;
    }

    /**
     * Проверка на принадлежность к имени класса
     * Второй аргумент позволяет выполнить сравнение и вывести его результат
     *
     * @param [type] $item
     * @param [type] $compare
     * @return void
     */
    public static function typeClass($item = null, $compare = null)
    {
        $type = self::type($item);

        if (!is_object($item)) {
            return null;
        }

        $name = get_class($item);
        $pos = mb_strrpos($name, '\\');
        $result = mb_strtolower(mb_substr($name, $pos !== false ? $pos + 1 : 0));

        if ($compare) {
            return $compare === $result ? true : null;
        }

        return $result;
    }

    /**
     * Проверка переменной на возможность его итерировать
     * Призвана заменить многократные проверки
     * type && ( type === array || type === object )
     *
     * @param [type] $item
     * @return void
     */
    public static function typeIterable($item = null)
    {
        return self::set($item) && self::typeOf($item, 'iterable');
    }

    /**
     * Возращает данные сервера
     *
     * @param [type] $name
     * @param [type] $from
     * @return void
     */
    public static function server($name, $from = null)
    {
        if ($from) {
            $name = mb_strtoupper($name);
            return isset($_SERVER[$name]) ? $_SERVER[$name] : null;
        }

        if ($name === 'root') {
            // \domains\isengine.org\public_html\
            $name = realpath((string) self::server('DOCUMENT_ROOT', true)) . DS;
        } elseif ($name === 'host') {
            // isengine.org
            $name = self::server('HTTP_HOST', true);
            // or SERVER_NAME
        } elseif ($name === 'protocol') {
            // HTTP/1.1
            $name = self::server('SERVER_PROTOCOL', true);
        } elseif ($name === 'port') {
            $name = self::server('SERVER_PORT', true);
        } elseif ($name === 'domain') {
            // http://0isengine.org
            $protocol = 'http';
            if (
                strtolower(substr((string) self::server('SERVER_PROTOCOL', true), 0, 5)) === 'https'
                || (self::server('HTTPS') && self::server('HTTPS', true) !== 'off')
                || self::server('SERVER_PORT', true) === 443
                || self::server('SERVER_PORT', true) === '443'
                || self::server('REQUEST_SCHEME', true) === 'https'
                || self::server('HTTP_X_FORWARDED_PORT', true) === 443
                || self::server('HTTP_X_FORWARDED_PORT', true) === '443'
                || self::server('HTTP_X_FORWARDED_PROTO', true) === 'https'
            ) {
                $protocol = 'https';
            }
            $name = $protocol . '://' . (
                extension_loaded('intl') ? idn_to_utf8(
                    (string) self::server('HTTP_HOST', true),
                    IDNA_DEFAULT,
                    version_compare(PHP_VERSION, '7.2.0', '<') ? INTL_IDNA_VARIANT_2003 : INTL_IDNA_VARIANT_UTS46
                ) : (string) self::server('HTTP_HOST', true)
            );
        } elseif ($name === 'request') {
            $name = urldecode((string) self::server('REQUEST_URI', true));
        } elseif ($name === 'method') {
            // get
            $name = strtolower((string) self::server('REQUEST_METHOD', true));
        } elseif ($name === 'ip') {
            // 127.0.0.1
            $name = self::server('REMOTE_ADDR', true);
        } elseif ($name === 'agent') {
            // Mozilla/5.0...
            $name = self::server('HTTP_USER_AGENT', true);
        } elseif ($name === 'referrer') {
            $name = self::server('HTTP_REFERER', true);
        } else {
            $name = null;
        }

        return $name;
    }

    /**
     * Приведение к типу:
     *     scalar   скаляный (строковый) тип - строка или число
     *     iterable итерируемый (объектный) тип - массив
     *     object   Объект/именованный не пустой массив
     *     array    Простой, неименованный не пустой массив
     *     numeric  число, в том числе строка записанная числом
     *     string   Строка
     *     true     Триггер/булев
     *     null     Пустой элемент, false, null, undefined, пустая строка или пустой объект/массив, но не ноль
     * данная функция возвращает значение переменной, приведенное к нужному типу
     * можно также использовать для сброса значений или очистки переменной
     *
     * @param [type] $item
     * @param [type] $type
     * @return void
     */
    public static function typeTo($item = null, $type = null)
    {
        if ($type === 'true') {
            $item = (bool) $item;
        } elseif ($type === 'string') {
            if (System::typeOf($item, 'iterable')) {
                $item = json_encode($item);
            } else {
                $item = (string) $item;
            }
        } elseif ($type === 'scalar') {
            $item = self::typeTo($item, System::type($item, 'numeric') ? 'numeric' : 'string');
            //if (System::typeOf($item, 'iterable')) {
            //    $item = json_encode($item);
            //} elseif (System::type($item, 'numeric')) {
            //    $item = preg_replace('/\s/u', null, $item);
            //    $item = (float) $item;
            //} else {
            //    $item = (string) $item;
            //}
        } elseif ($type === 'numeric') {
            $item = preg_replace('/\s/u', '', (string) $item);
            if (mb_strpos($item, '.') === false) {
                $item = (int) $item;
            } else {
                $item = (float) $item;
            }
        } elseif (
            $type === 'array'
            || $type === 'iterable'
        ) {
            $item = $item ? (array) $item : [];
        } elseif ($type === 'object') {
            $item = $item ? (object) $item : (object) [];
        } else {
            $item = null;
        }

        //var_dump($item);
        //System::debug(
        //    'item : ' . print_r($item, 1),
        //    'to__ : ' . $type,
        //    'type : ' . System::type($item)
        //);

        return $item;
    }

    /**
     * Делает цикл в заданном количестве итераций
     * с использованием пользовательской функции,
     * куда передается текущая позиция цикла, начиная с 0
     * вторым аргументом пользовательская функция может принять
     * переменную или объект, с которым будет работать
     *
     * простой пример использования
     * System::loop(5, function($c) {
     *   echo $c;
     * });
     *
     * пример использования без аргументов
     * System::loop(5, function() {
     *   echo '<p><br></p>';
     * });
     *
     * пример создания массива
     * $a = [];
     * $result = System::loop(5, function($c, $a) {
     *   $a[] = $c;
     *   return $a;
     * });
     *
     * другой пример создания массива
     * $i = [];
     * System::loop(5, function($c) use (&$i) {
     *   $i[] = $c;
     * });
     *
     * пример создания массива с использованием другого массива
     * $a = [];
     * $i = ['a', 'b', 'c', 'd', 'e'];
     * $result = System::loop(5, function($c, $a) use ($i) {
     *   $a[] = $i[$c];
     *   return $a;
     * });
     *
     * пример использования цикла для работы с другим массивом
     * $i = ['a', 'b', 'c', 'd', 'e'];
     * System::loop(5, function($c) use (&$i) {
     *   $i[$c] .= $c;
     * });
     *
     * пример создания массива из строки
     * $i = 'abcde';
     * $result = System::loop(5, function($c, $result) use ($i) {
     *   $result[] = Strings::get($i, $c, 1);
     *   return $result;
     * });
     *
     * пример создания строки из массива
     * $i = ['a', 'b', 'c', 'd', 'e'];
     * $result = System::loop(5, function($c, $result) use ($i) {
     *   $result .= $i[$c];
     *   return $result;
     * });
     *
     * пример создания строки
     * $a = null;
     * $result = System::loop(5, function($c, $a) {
     *   $a .= $c;
     *   return $a;
     * });
     *
     * @param [type] $num
     * @param [type] $callback
     * @return void
     */
    public static function loop($num, $callback)
    {
        if ($num < 1) {
            return;
        }

        $count = 0;

        while ($count < $num) {
            $item = call_user_func($callback, $count, $item = null);
            $count++;
        }

        return $item;
    }

    /**
     * Вспомогательная функция, для отладки - выводит строку для проверки
     * default !q !console !dump !stop !hide
     *
     * @param [type] ...$item
     * @return void
     */
    public static function debug(...$item)
    {
        $c = count($item);
        $action = null;

        $array = [
            'default'  => [ '<pre>', '<br>', '</pre>' ],
            '!q'       => [ '[', '', ']<br>' ],
            '!code'    => [ '<pre>', '<br>', '</pre>' ],
            '!console' => [ '<script>console.log(\'', '\n', '\');</script>' ],
            '!hide'    => [ '<!--', "\r\n", '-->' ]
        ];

        if ($c > 1 && is_string(end($item)) && end($item)[0] === '!') {
            $action = array_pop($item);
        }
        $array = isset($array[$action]) ? $array[$action] : $array['default'];

        echo $array[0];

        foreach ($item as $i) {
            if ($action === '!console') {
                $i = json_encode(print_r($i, true));
            } elseif ($action === '!dump') {
                $i = var_export($i, true);
            } elseif ($action === '!code') {
                $i = print_r(htmlentities($i), true);
            } else {
                $i = print_r($i, true);
            }

            echo $i . $array[1];
        }
        unset($i, $item);

        echo $array[2];

        if ($action === '!stop') {
            exit;
        }
    }
}
