<?php

namespace is\Helpers;

class Objects
{
    /**
     * Функция создает массив данных по заданной структуре
     * с указанным заполнением
     *
     * @param [type] $structure
     * @param [type] $fill
     * @return void
     */
    public static function create($structure, $fill = null)
    {
        $result = self::convert($structure);

        if ($fill) {
            $result = self::merge(
                $result,
                $fill,
                true
            );
        }

        return $result;
    }

    /**
     * Функция создает массив данных по заданной структуре
     * но теперь это структура индексов
     * с указанным заполнением
     *
     * @param [type] $structure
     * @param [type] $fill
     * @return void
     */
    public static function createByIndex($structure, $fill = null)
    {
        $result = self::join(
            self::convert($structure),
            null
        );

        if ($fill) {
            $result = self::merge(
                $result,
                $fill
            );
        }

        return $result;
    }

    /**
     * Функция проверяет, является ли массив ассоциативным
     *
     * @param [type] $item
     * @return void
     */
    public static function associate($item)
    {
        $result = null;

        if (System::typeData($item, 'object')) {
            foreach (array_keys($item) as $value) {
                if (!is_int($value)) {
                    $result = true;
                    break;
                }
            }
            unset($value);
        }

        return $result;
    }

    /**
     * Функция проверяет, является ли массив состоящим из цифр
     *
     * @param [type] $item
     * @return void
     */
    public static function numeric($item)
    {
        $result = true;

        if (System::typeData($item, 'object')) {
            foreach ($item as $value) {
                if (!is_int($value)) {
                    $result = null;
                    break;
                }
            }
            unset($value);
        }

        return $result;
    }

    /**
     * Функция преобразует любые входные данные в системный объект
     *
     * @param [type] $item
     * @return void
     */
    public static function convert($item)
    {
        $type = System::typeData($item);

        if ($type === 'string') {
            $item = Parser::fromString($item, ['key' => null, 'clear' => null, 'simple' => true]);
        } elseif ($type === 'json') {
            $item = Parser::fromJson($item);
        } elseif (!$type && System::set($item)) {
            $item = is_object($item) ? json_decode(json_encode($item), true) : [$item];
        }

        return $item;
    }

    /**
     * Функция возврата ключей массива
     *
     * @param [type] $item
     * @return void
     */
    public static function keys($item)
    {
        return array_keys($item);
    }

    /**
     * Функция возврата значений массива
     *
     * @param [type] $item
     * @return void
     */
    public static function values($item)
    {
        return array_values($item);
    }

    /**
     * Функция проверки наличия строки или символа в заданном массиве
     * проверка по нестрогому соответствию, т.е. 3 === '3'
     * $result = in_array($needle, $haystack);
     * исходный вариант в ряде случаев осуществляет неправильную проверку,
     * например значение 0 в haystack (чаще в ключах массивов)
     * дает постоянное совпадение с любой не numeric строкой
     * такое поведение недопустимо, поэтому делаем дополнительную проверку
     * и вводим ряд условий
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @return void
     */
    public static function match($haystack, $needle)
    {
        $type = System::type($needle);

        if ($type === 'numeric') {
            $result = in_array((float)$needle, $haystack, true) || in_array((string)$needle, $haystack, true);
        } elseif ($type === 'string') {
            $result = in_array($needle, $haystack, true);
        } else {
            $result = in_array($needle, $haystack);
        }

        return !System::set($result) || $result === false ? null : true;
    }

    /**
     * Функция проверки наличия строки или символа в ключах заданного массива
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @return void
     */
    public static function matchByIndex($haystack, $needle)
    {
        $haystack = (array) self::keys($haystack);

        $type = System::type($needle);

        if ($type === 'numeric') {
            $result = in_array((float) $needle, $haystack, true) || in_array((string) $needle, $haystack, true);
        } elseif ($type === 'string') {
            $result = in_array($needle, $haystack, true);
        } else {
            $result = in_array($needle, $haystack);
        }

        return !System::set($result) || $result === false ? null : true;
    }

    /**
     * Функция поиска строки или символа в заданном массиве
     * если задан position, то он ищет соответствие строки в заданном ключе
     * положительное значение - поиск с начала, от 0
     * отрицательное значение - поиск с конца, от -1
     * если position не задан, то возвращает первый ключ/индекс значение с начала
     * специальное значение 'r' задает возврат индекса/ключа последнего значения в массиве
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @param [type] $position
     * @return void
     */
    public static function find($haystack, $needle, $position = null)
    {
        $find = array_keys($haystack, $needle);
        //$find = self::keys(self::filter($haystack, $needle));
        //echo 'FIND:' . print_r($find, 1) . '<hr>';

        if (System::set($position) && $position !== true) {
            if ($position < 0) {
                $position = (int) self::len($haystack) + $position;
            }
            return in_array($position, $find) === false ? null : true;
        } elseif ($position === true) {
            return self::last($find, 'value');
        } else {
            return self::first($find, 'value');
        }
    }

    /**
     * Функция возвращает срез массива по указанному индексу (позиции) и заданной длине
     * если длина задана, то она смещается
     * положительное значение - вперед
     * отрицательное значение - назад
     * если длина не задана, то возвращается вся строка от индекса и до конца
     * специальное значение position = true задает $length от конца массива
     *
     * @param [type] $haystack
     * @param [type] $index
     * @param [type] $length
     * @param [type] $position
     * @return void
     */
    public static function get($haystack, $index, $length = null, $position = null)
    {
        $len = System::set($length);

        if ($length && !$position) {
            if ($length < 0) {
                $idx = $index;
                $index += $length + 1;
                $length = abs($length);
                if ($idx > 0 && $index < 0) {
                    $length = $idx + 1;
                    $index = 0;
                } elseif ($length > self::len($haystack)) {
                    $length = $idx + 1;
                }
            }
            return array_slice($haystack, $index, $length, true);
        } elseif ($position) {
            return array_slice($haystack, (int) $index, $length ? 0 - $length : null, true);
        } else {
            return array_slice($haystack, (int) $index, $length, true);
        }
    }

    /**
     * Функция удаления части массива по указанному индексу (позиции) и заданной длине
     * если длина задана, то она смещается
     * положительное значение - вперед
     * отрицательное значение - назад
     * если длина не задана, то возвращается вся строка от индекса и до конца
     * специальное значение position = true задает $length от конца массива
     *
     * @param [type] $haystack
     * @param [type] $index
     * @param [type] $length
     * @param [type] $position
     * @return void
     */
    public static function cut($haystack, $index, $length = null, $position = null)
    {
        $len = (int) self::len($haystack);

        if (!$length) {
            $length = $position ? 0 : $len;
        }

        $first = $index < 0 ? $len + $index : $index;
        $last = $first + ($length < 0 ? $length + 1 : $length);

        if ($position) {
            $last = $len - abs($length);
        }

        if ($first > $last) {
            $point = $first + 1;
            $first = $last;
            $last = $point;
            unset($point);
        }

        if ($first < 0) {
            $first = 0;
        } elseif ($first > $len) {
            $first = $len;
        }
        if ($last < 0) {
            $last = 0;
        } elseif ($last > $len) {
            $last = $len;
        }

        return array_replace(
            array_slice($haystack, 0, $first, true),
            array_slice($haystack, $last, null, true)
        );
    }

    /**
     * Функция, которая возвращает срез массива до первого заданного значения
     * включение include позволяет включить в массив найденное значение
     * специальное значение reverse возвращает массив до последнего заданного значения
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @param [type] $include
     * @param [type] $reverse
     * @return void
     */
    public static function before($haystack, $needle, $include = null, $reverse = null)
    {
        $key = self::find($haystack, $needle, $reverse ? 'r' : null);

        if (!System::set($key)) {
            return $haystack;
        } elseif (!$key) {
            return null;
        }

        $keys = self::keys($haystack);
        $pos = (int) self::find($keys, $key);

        $result = self::get($keys, 0, $pos + ($include ? 1 : 0));

        return array_intersect_key($haystack, self::join($result, null));
    }

    /**
     * Функция, которая возвращает срез массива после первого заданного значения
     * включение include позволяет включить в массив найденное значение
     * специальное значение reverse возвращает массив после последнего заданного значения
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @param [type] $include
     * @param [type] $reverse
     * @return void
     */
    public static function after($haystack, $needle, $include = null, $reverse = null)
    {
        $key = self::find($haystack, $needle, $reverse ? 'r' : null);

        if (!System::set($key)) {
            return $haystack;
        } elseif (!$key) {
            return null;
        }

        $keys = self::keys($haystack);
        $pos = (int) self::find($keys, $key);

        $result = self::get($keys, $pos + 1 - ($include ? 1 : 0));

        return array_intersect_key($haystack, self::join($result, null));
    }

    /**
     * Функция добавления значений в начало или в конец массива
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @param [type] $recursive
     * @return void
     */
    public static function add($haystack, $needle, $recursive = null)
    {
        $haystack = self::convert($haystack);
        $needle = self::convert($needle);

        return $recursive ? array_merge_recursive($haystack, $needle) : array_merge($haystack, $needle);
    }

    /**
     * Функция удаления заданных значений из массива
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @return void
     */
    public static function remove($haystack, $needle)
    {
        $haystack = (array) self::convert($haystack);
        $needle = self::convert($needle);

        return array_diff($haystack, $needle);
    }

    /**
     * Функция удаления заданных ключей из массива
     * теперь может работать рекурсивано
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @param [type] $recursive
     * @return void
     */
    public static function removeByIndex($haystack, $needle, $recursive = null)
    {
        $haystack = self::convert($haystack);
        $needle = self::convert($needle);

        foreach ((array) $needle as $item) {
            unset($haystack[$item]);
        }
        unset($item);

        if ($recursive) {
            foreach ((array) $haystack as &$item) {
                if (is_array($item)) {
                    $item = self::removeByIndex($item, $needle, $recursive);
                }
            }
            unset($item);
        }

        return $haystack;
    }

    /**
     * Функция разворачивает массив
     *
     * @param [type] $item
     * @return void
     */
    public static function reverse($item)
    {
        return array_reverse($item);
    }

    /**
     * Функция возврата первого значения массива
     *
     * @param [type] $item
     * @param [type] $result
     * @return void
     */
    public static function first($item, $result = null)
    {
        if (!is_array($item)) {
            return;
        }

        $key = null;
        $val = null;

        if (version_compare(PHP_VERSION, '7.3.0', '<')) {
            foreach ($item as $k => $i) {
                if ($result !== 'value') {
                    $key = $k;
                }
                if ($result !== 'key') {
                    $val = $i;
                }
                break;
            }
        } else {
            if ($result !== 'value') {
                $key = array_key_first($item);
            }
            if ($result !== 'key') {
                $val = reset($item);
            }
        }

        if ($result === 'key') {
            return $key;
        } elseif ($result === 'value') {
            return $val;
        } else {
            return ['key' => $key, 'value' => $val];
        }
    }

    /**
     * Функция возврата последнего значения массива
     *
     * @param [type] $item
     * @param [type] $result
     * @return void
     */
    public static function last($item, $result = null)
    {
        if (!is_array($item)) {
            return;
        }

        $key = null;
        $val = null;

        if (version_compare(PHP_VERSION, '7.3.0', '<')) {
            $item = array_slice($item, -1, 1, true);
            if ($result !== 'value') {
                $key = key($item);
            }
            if ($result !== 'key') {
                $val = reset($item);
            }
        } else {
            if ($result !== 'value') {
                $key = array_key_last($item);
            }
            if ($result !== 'key') {
                $val = end($item);
            }
        }

        if ($result === 'key') {
            return $key;
        } elseif ($result === 'value') {
            return $val;
        } else {
            return ['key' => $key, 'value' => $val];
        }
    }

    /**
     * Функция замены первого значения массива
     *
     * @param [type] $item
     * @param [type] $data
     * @return void
     */
    public static function refirst(&$item, $data)
    {
        if (!$item) {
            return;
        }

        $key = self::first($item, 'key');
        $item[$key] = $data;
    }

    /**
     * Функция замены последнего значения массива
     *
     * @param [type] $item
     * @param [type] $data
     * @return void
     */
    public static function relast(&$item, $data)
    {
        if (!$item) {
            return;
        }

        $key = self::last($item, 'key');
        $item[$key] = $data;
    }

    /**
     * Функция удаления первого значения массива
     *
     * @param [type] $item
     * @return void
     */
    public static function unfirst(&$item)
    {
        return array_slice($item, 1, null, true);
    }

    /**
     * Функция удаления последнего значения массива
     *
     * @param [type] $item
     * @return void
     */
    public static function unlast(&$item)
    {
        return array_slice($item, 0, -1, true);
    }

    /**
     * Функция возврата длины массива
     * Добавлен второй аргумент, позволяющий считать длину многомерного массива
     * Обычно считаются элементы, являющиеся вложенными массивами,
     * но в режиме рекурсии они не подсчитываются
     *
     * @param [type] $item
     * @param [type] $recursive
     * @return void
     */
    public static function len($item, $recursive = null)
    {
        if (!System::typeData($item, 'object')) {
            $item = self::convert($item);
        }

        if (!$item) {
            return;
        }

        if (!$recursive) {
            return count($item);
        }

        $c = 0;

        foreach ($item as $i) {
            if (is_array($i)) {
                $c += (int) self::len($i, true);
            } else {
                $c++;
            }
        }
        unset($i);

        return $c;
    }

    /**
     * Функция которая разделяет массив по-очереди на ключи и значения
     *
     * @param [type] $array
     * @return void
     */
    public static function split($array)
    {
        $result = [];

        $i = 0;
        $key = null;
        foreach ($array as $item) {
            if ($i % 2 === 0) {
                $key = $item;
            } else {
                if (is_float($key)) {
                    $key = (string) $key;
                }
                $result[$key] = $item;
            }
            $i++;
        }
        unset($key, $item, $i);

        return $result;
    }

    /**
     * Функция создания массива из двух массивов
     * первый используется в качестве ключей
     * второй - в качестве значений
     *
     * если длина массивов разная, то
     * итоговый массив создается по длине массива ключей
     * дополняясь элементами default
     *
     * если в качестве значения передан не массив,
     * то массив ключей целиком заполняется переданным значением
     *
     * @param [type] $keys
     * @param [type] $values
     * @param [type] $default
     * @return void
     */
    public static function join($keys, $values, $default = null)
    {
        if (System::type($values) !== 'array') {
            return self::join($keys, [], $values);
        }

        if (System::type($keys) !== 'array' || !count($keys)) {
            return array_values($values);
        }

        $keys = (array) self::clear($keys);
        $lkeys = (int) self::len($keys);
        $lvalues = (int) self::len($values);

        if ($lkeys > $lvalues) {
            // СТАРОЕ ПОВЕДЕНИЕ
            //$keys = array_slice($keys, 0, $lvalues);
            // НОВОЕ ПОВЕДЕНИЕ
            $values = array_pad($values, $lkeys, $default);
        } elseif ($lvalues > $lkeys) {
            $values = array_slice($values, 0, $lkeys);
        }

        return array_combine($keys, $values);
    }

    /**
     * Функция объединяет многомерный массив в одномерный
     *
     * @param [type] $item
     * @param array $result
     * @return void
     */
    public static function combine($item, $result = [])
    {
        if (!System::typeIterable($item)) {
            return $item;
        }

        foreach ($item as $i) {
            if (System::typeIterable($i)) {
                $i = self::combine($i);
            }
            if (System::typeIterable($i)) {
                $result = array_merge($result, $i);
            } else {
                $result[] = $i;
            }
        }
        unset($i);

        return $result;
    }

    /**
     * Функция объединяет многомерный массив в одномерный с сохранением ключей
     *
     * @param [type] $item
     * @param array $result
     * @return void
     */
    public static function combineByIndex($item, $result = [])
    {
        if (!System::typeIterable($item)) {
            return $item;
        }

        foreach ($item as $k => $i) {
            if (System::typeIterable($i)) {
                $i = self::combineByIndex($i);
                $result = self::merge($result, $i);
            } else {
                $result[$k] = $i;
            }
        }
        unset($i);

        return $result;
    }

    /**
     * Функция объединения двух массивов в один
     *
     * @param [type] $item
     * @param [type] $merge
     * @param [type] $recursive
     * @return void
     */
    public static function merge($item, $merge, $recursive = null)
    {
        if (System::type($merge) !== 'array' || !count($merge)) {
            return $item;
        }

        if ($recursive) {
            return array_replace_recursive($item, $merge);
        } else {
            return array_replace($item, $merge);
        }
    }

    /**
     * это простая замена foreach
     * позволяет перебирать элементы объекта или массива
     * только в том случае, если он не пустой
     *
     * теперь он еще и позицию показывает - first, last или alone
     *
     * сделана в основном для того, чтобы облегчить код
     * например:
     * Objects::each($sets['form'], function($i, $k){
     *   $this->eget('form')->addCustom($k, $i);
     * });
     * вместо:
     * if (System::typeIterable($sets['form'])) {
     *   foreach ($sets['form'] as $key => $item) {
     *     $this->eget('form')->addCustom($key, $item);
     *   }
     *   unset($key, $item);
     * }
     *
     * теперь добавлен последний аргумент, который позволяет
     * обрабатывать пустые массивы
     *
     * @param [type] $item
     * @param [type] $callback
     * @param [type] $ignore
     * @return void
     */
    public static function each(&$item, $callback, $ignore = null)
    {
        if (!is_array($item) || (!$ignore && !System::typeIterable($item))) {
            return;
        }

        $target = [
            self::first($item, 'key'),
            self::last($item, 'key'),
            self::len($item)
        ];

        foreach ($item as $key => &$value) {
            $position = null;
            if ($target[2] === 1) {
                $position = 'alone';
            } elseif ($key === $target[0]) {
                $position = 'first';
            } elseif ($key === $target[1]) {
                $position = 'last';
            }

            $value = call_user_func($callback, $value, $key, $position);
        }
        unset($key, $value);

        return $item;
    }

    /**
     * это универсальная замена foreach, которая управляет элементами в текущем массиве
     * item - входящий массив или объект
     * parameter - параметр, который влияет на поведение в случае,
     * когда значение 'item' = 'false' (но не null и не любое другое)
     *     filter - передает в качестве результата копию исходного массива и удаляет из него текущий элемент
     *     break - прерывает цикл
     *     continue - переходит к следующей итерации
     * специальное значение параметра в виде массива или объекта,
     * передает в функцию этот объект третьим параметром (не забудьте сделать его ссылкой) и вы можете изменять его
     * callback - callback-функция, как правило анонимная,
     * которая работает в итерации входящего массива, принимает параметры
     *   текущее значение
     *   текущий ключ
     *   возвращаемый массив или объект (не забудьте сделать его ссылкой), если он передан в параметр
     * любой из этих параметров можно не указывать, и тогда они не будут участвовать в процессе
     * эта функция возвращает результат, который записывается вместо текущего значения
     *
     * если вы хотите использовать входящий массив или строку,
     * используйте их, переданными в виде объекта/массива через параметр
     * например: each($obj, [ 'str' => null ], function ($v, $k, $p){ $p['str'] .= '...'; });
     *
     * производительность этой функции медленнее в 1.5-2.5 раза по сравнению с foreach,
     * но на сложных расчетах внутри итерации ее скорость становится такой же, как у встроенной функции
     * и для мелких итераций ее скорость остается почти такой же
     * и она расходует столько же памяти, как встроенная функция
     * удобство ее использования в том, что она универсальна как для php, так и для js
     *
     * @param [type] $item
     * @param [type] $parameters
     * @param [type] $callback
     * @return void
     */
    public static function eachOf(&$item, $parameters, $callback)
    {
        $type = System::typeOf($parameters);

        if ($type === 'iterable') {
            foreach ($item as $key => &$value) {
                call_user_func_array($callback, [$value, $key, &$parameters]);
            }
            return $parameters;
        } elseif (!$type) {
            foreach ($item as $key => &$value) {
                $value = call_user_func($callback, $value, $key);
            }
            unset($key, $value);
        } else {
            foreach ($item as $key => &$value) {
                $result = call_user_func($callback, $value, $key);
                if ($result === false) {
                    if ($parameters === 'filter') {
                        unset($item[$key]);
                        continue;
                    } elseif ($parameters === 'break') {
                        break;
                    } elseif ($parameters === 'continue') {
                        continue;
                    }
                } else {
                    $value = $result;
                }
            }
            unset($key, $value);
        }

        return $item;
    }

    /**
     * Функция, аналогичная each, но с рекурсией
     *
     * @param [type] $item
     * @param [type] $callback
     * @param [type] $ignore
     * @return void
     */
    public static function recurse(&$item, $callback, $ignore = null)
    {
        if (!is_array($item) || (!$ignore && !System::typeIterable($item))) {
            return;
        }

        array_walk_recursive($item, function (&$value, $key) use ($callback) {
            $value = call_user_func($callback, $value, $key);
        });
    }

    /**
     * Функция очищает массив от пустых элементов
     *
     * @param [type] $item
     * @param [type] $unique
     * @return void
     */
    public static function clear(&$item, $unique = null)
    {
        if (!System::typeOf($item, 'iterable')) {
            return $item;
        }

        foreach ($item as $k => &$i) {
            if (System::typeOf($i, 'iterable')) {
                $i = self::clear($i, $unique);
            }
            if (!System::set($i)) {
                unset($item[$k]);
            }
        }
        unset($k, $i);

        if ($unique) {
            $item = array_unique($item);
        }

        return $item;
    }

    /**
     * Функция убирает повторяющиеся элементы в массиве
     *
     * @param [type] $item
     * @return void
     */
    public static function unique($item)
    {
        return array_unique($item);
    }

    /**
     * Функция сортировки массива
     * всегда используется NATCASESORT без учета регистра
     * вторым аргументом можно задать сортировку по ключам
     * третьим аргументом можно принудительно задать тип массива
     *
     * @param [type] $haystack
     * @param boolean $keys
     * @param string $associate
     * @return void
     */
    public static function sort($haystack, $keys = false, $associate = 'default')
    {
        $associate = $associate === 'default' ? self::associate($haystack) : $associate;

        $numeric = $keys ? !$associate : self::numeric($haystack);

        if ($keys) {
            ksort($haystack, $numeric ? SORT_NUMERIC : SORT_NATURAL | SORT_FLAG_CASE);
        } else {
            asort($haystack, $numeric ? SORT_NUMERIC : SORT_NATURAL | SORT_FLAG_CASE);
        }

        if ($associate) {
            return $haystack;
        } else {
            //$result = [];
            //foreach ($haystack as $i) {
            //    $result[] = $i;
            //}
            //unset($i);
            //return $result;
            return self::values($haystack);
        }
    }

    /**
     * Функция сортировки массива в случайном порядке
     * с сохранением ключей в ассоциативном массиве
     *
     * @param [type] $haystack
     * @return void
     */
    public static function random($haystack)
    {
        $associate = self::associate($haystack);

        if ($associate) {
            $result = [];
            $keys = (array) self::keys($haystack);
            shuffle($keys);
            foreach ($keys as $key) {
                $result[$key] = $haystack[$key];
            }
            unset($key);
            return $result;
        } else {
            shuffle($haystack);
            return $haystack;
        }
    }

    /**
     * Функция возвращает массив, содержащий различия между двумя массивами
     *
     * @param [type] $haystack
     * @param [type] $needle
     * @return void
     */
    public static function difference($haystack, $needle)
    {
        return array_diff($haystack, $needle);
    }

    /**
     * Функция которая разделяет массив на два массива по значению
     *
     * @param [type] $array
     * @param [type] $splitter
     * @param [type] $offset
     * @return void
     */
    public static function pairs($array, $splitter, $offset = null)
    {
        $key = self::find($array, $splitter);

        if (!System::set($key)) {
            return [ $array, [] ];
        }

        $keys = self::keys($array);
        $pos = (int) self::find($keys, $key);

        $before = $offset < 0 ? 1 : 0;
        $after = $offset > 0 ? 0 : 1;

        $first = self::get($keys, 0, $pos + $before);
        $last = self::get($keys, $pos + $after);

        return [
            array_intersect_key($array, self::join($first, null)),
            array_intersect_key($array, self::join($last, null))
        ];
    }

    /**
     * Функция которая разделяет массив на два массива по индексу
     *
     * @param [type] $array
     * @param [type] $splitter
     * @param [type] $offset
     * @return void
     */
    public static function pairsByIndex($array, $splitter, $offset = null)
    {
        $keys = self::keys($array);
        $pos = (int) self::find($keys, $splitter);

        if (!System::set($pos)) {
            return [ $array, [] ];
        }

        $before = $offset < 0 ? 1 : 0;
        $after = $offset > 0 ? 0 : 1;

        $first = self::get($keys, 0, $pos + $before);
        $last = self::get($keys, $pos + $after);

        return [
            array_intersect_key($array, self::join($first, null)),
            array_intersect_key($array, self::join($last, null))
        ];
    }

    /**
     * Функция которая меняет значения и ключи массива местами
     *
     * @param [type] $array
     * @return void
     */
    public static function flip($array)
    {
        return array_flip($array);
    }

    /**
     * Функция которая производит объединение данных в многомерных массивах или объектах
     * на входе нужно указать:
     *   целевой массив или объект, которЫЙ будем заполнять - $haystack
     *   и массив или объект, который содержит ключи, которЫМИ будем заполнять haystack - $map
     *   третий, необязательный, аргумент - это значение
     * ТЕПЕРЬ ПОВЕДЕНИЕ ТАКОВО, ЧТО ПО-УМОЛЧАНИЮ ПУСТЫЕ ЗНАЧЕНИЯ НЕ ЗАПОЛНЯЮТСЯ!
     *
     * Например, если указать:
     * inject(['data' => null], ['a', 'b', 'c'], 'value')
     * то на выходе получим такой массив:
     * [ 'data' => ['a' => ['b' => ['c' => 'value']]] ];
     *
     * при этом, особенность данной функции в том, что она дополняет массив и не стирает другие имеющиеся в нем поля
     *
     * @param [type] $haystack
     * @param [type] $map
     * @param [type] $value
     * @return void
     */
    public static function inject($haystack, $map, $value = null)
    {
        if (!is_array($haystack) || !is_array($map)) {
            return null;
        }

        $map = (array) self::reset(self::clear($map));

        $map = array_reverse($map);
        $c = count($map);
        $item = $value;

        if (!empty($c) && is_int($c)) {
            for ($i = 0; $i < $c; $i++) {
                $k = array_shift($map);
                $item = [$k => $item];
            }
        }

        unset($map, $c, $i, $value);

        if (!$item) {
            $item = [];
        }

        //return array_merge_recursive($haystack, $item);
        return array_replace_recursive($haystack, $item);
    }

    /**
     * Функция которая производит извлечение данных в многомерных массивах или объектах
     * на входе нужно указать:
     *   целевой массив или объект, ИЗ котороГО будем извлекать данные - $haystack
     *   и массив или объект, согласно котороМУ будем извлекать эти данные - $map
     *
     * Третий аргумент может принимать значение true
     * и тогда результирующий массив будет преобразован в объект и наоборот
     *
     * Если вы хотите извлечь значение из многомерного массива, использовать так:
     * $arr = objectExtract($arr, ['field', 'field', 'field']);
     * Например, если $haystack = ['a' => ['b' => ['c' => 1, 'd' => 2]]]
     * и вам надо извлечь d, то используйте такой вызов:
     * $arr = objectExtract($haystack, ['a', 'b', 'd']);
     *
     * на выходе отдает готовый массив $haystack
     *
     * @param [type] $haystack
     * @param [type] $map
     * @return void
     */
    public static function extract($haystack, $map)
    {
        $map = (array) self::reset(self::clear($map));

        foreach ($map as $i) {
            if (
                System::type($haystack, 'array')
                //&& System::set($haystack[$i])
            ) {
                $haystack = isset($haystack[$i]) ? $haystack[$i] : null;
            } elseif (
                System::type($haystack, 'object')
                //&& System::set($haystack->$i)
            ) {
                $haystack = $haystack->$i;
            } else {
                $haystack = null;
                break;
            }
        }

        return $haystack;
    }

    /**
     * Функция которая удаляет ключ и значение по заданной карте в многомерных массивах или объектах
     * на входе нужно указать:
     *   целевой массив или объект, в котором будем удалять ключ - $haystack
     *   и массив или объект, который содержит ключи, по которым будем искать путь - $map
     *
     * Например, если указать:
     * delete(['a' => ['b' => ['c' => 'value']]], ['a', 'b', 'c']);
     * то на выходе получим такой массив:
     * ['a' => ['b' => []]];
     *
     * @param [type] $haystack
     * @param [type] $map
     * @return void
     */
    public static function delete(&$haystack, $map)
    {
        if (!is_array($haystack) || !is_array($map)) {
            return null;
        }

        $map = (array) self::reset(self::clear($map));

        $c = count($map) - 1;
        $current = &$haystack;

        foreach ($map as $key => $item) {
            if (!is_array($current)) {
                break;
            }
            if ($key === $c) {
                unset($current[$item]);
            } else {
                $current = &$current[$item];
            }
        }
        unset($key, $item);

        return $haystack;
    }

    /**
     * Функция для вложенных массивов
     * переназначает ключи главного массива
     * по значению указанного поля внутреннего массива
     *
     * @param [type] $item
     * @param [type] $name
     * @return void
     */
    public static function remap(&$item, $name)
    {
        foreach ($item as $k => $i) {
            if (!is_array($i)) {
                continue;
            }
            $key = $i[$name];
            if (!System::set($key)) {
                continue;
            }
            $item[$key] = $i;
            unset($item[$k]);
        }
        unset($k, $i);

        return $item;
    }

    /**
     * Функция сбрасывает ключи массива
     *
     * @param [type] $item
     * @return void
     */
    public static function reset($item)
    {
        return self::values($item);
    }
}
