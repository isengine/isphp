<?php

namespace is\Helpers;

class Paths
{
    /**
     * Функция парсинга url-адреса
     * первый аргумент - адрес для парсинга,
     * второй - если нужно вернуть только одну часть
     * scheme, host, port, user, password, path, query, fragment
     *
     * @param [type] $url
     * @param [type] $get
     * @return void
     */
    public static function parseUrl($url, $get = null)
    {
        $parse = parse_url($url);

        $parse['password'] = isset($parse['pass']) ? $parse['pass'] : null;
        unset($parse['pass']);

        return $get ? $parse[$get] : $parse;
    }

    /**
     * Функция парсинга имени и адреса файла
     * первый аргумент - адрес для парсинга,
     * второй - если нужно вернуть только одну часть
     * path, file, name, extension
     *
     * @param [type] $url
     * @param [type] $get
     * @return void
     */
    public static function parseFile($url, $get = null)
    {
        $parse = Objects::createByIndex(
            ['extension', 'dirname', 'filename', 'basename'],
            pathinfo($url)
        );

        $isfile = $parse['extension'] ? true : null;
        $dirname =
            $parse['dirname'] && $parse['dirname'] !== '.' && $parse['dirname'] !== '..'
            ? $parse['dirname'] . DS : null;

        if ($isfile) {
            $result = [
                'name' => $parse['filename'],
                'extension' => $parse['extension'],
                'file' => $parse['basename'],
                'path' => $dirname
            ];
        } else {
            $result = [
                'name' => null,
                'extension' => null,
                'file' => null,
                'path' => $dirname . $parse['basename'] . DS
            ];
        }

        // данный параметр позволяет обнаружить файл в системе
        // однако она вызывает предупреждение, если права на доступ ограничены,
        // например когда система ищет файл в корневой директории
        // к тому же этот параметр нигде не используется,
        // он был сделан про запас, и потому принято решение его отключить
        // -
        //if (!$get || $get === 'exists') {
        //    $result['exists'] = file_exists($url);
        //}

        unset($parse);

        return $get ? $result[$get] : $result;
    }

    /**
     * Корректно преобразует заданный путь в относительный
     * оставляет начало в абсолютном пути
     * узнаем, файл это или папка
     * и определяем, содержит ли путь фрагмент
     * здесь мы задаем поиск только в пределах папки System::server('root'),
     * так как все url должны находиться именно там
     *
     * @param [type] $path
     * @param [type] $host
     * @return void
     */
    public static function prepareUrl($path = null, $host = null)
    {
        $nofolder =
            self::parseFile(realpath(System::server('root') . $path), 'file')
            || Strings::match($path, '#')
            || Strings::match($path, '?');
        //$nofolder = self::parseFile($path, 'file') || Strings::match($path, '#') || Strings::match($path, '?');

        $absolute = self::absolute($path);

        $path = self::clearSlashes(self::toUrl($path));

        if ($path) {
            return (
                    $absolute
                    ? null
                    : ($host ? System::server('domain') : null) . '/'
                )
                . $path
                . (!$nofolder ? '/' : null);
        } else {
            return '/';
        }
    }

    /**
     * Корректно преобразует заданный абсолютный путь в относительный
     * относительно базовой директории хоста
     *
     * @param [type] $path
     * @return void
     */
    public static function realToRelativeUrl($path = null)
    {
        $host = DI;
        if (Strings::find($path, $host) === 0) {
            return Strings::get($path, (int) Strings::len($host) - 1);
        }
    }

    /**
     * Преобразует путь в url
     *
     * @param [type] $path
     * @return void
     */
    public static function toUrl($path)
    {
        return preg_replace('/\:(?!(\/|\\\\))+|\\\\|\//u', '/', $path);
    }

    /**
     * Преобразует путь в real
     *
     * @param [type] $path
     * @return void
     */
    public static function toReal($path)
    {
        return preg_replace('/\:(?!(\/|\\\\))+|\\\\|\//u', DS, $path);
    }

    /**
     * Корректно выбирает родительский каталог в заданном пути
     * второй аргумент позволяет выбрать уровень смещения родителя
     *
     * @param [type] $path
     * @param [type] $level
     * @return void
     */
    public static function parent($path, $level = null)
    {
        $start = Strings::first($path);
        //$real = Strings::match($path, DS);
        $url = Strings::match($path, '://');
        //$array = Strings::split($path, $real ? '\\\\' : '\/');
        $array = Strings::split($path, $url ? '\/' : '\\' . DS);

        //echo '[' . print_r($array, 1) . ']<br>';

        $first = Objects::first($array, 'value');
        if (!Objects::last($array, 'value')) {
            $level++;
        }
        $array = Objects::get($array, 0, $level + 1, 'r');

        if (!System::set($array)) {
            $array = $start === '\\' || $start === '/' ? true : null;
        } elseif (Objects::len($array) === 1) {
            $f = Objects::first($array, 'value');
            if (Strings::match($f, ':')) {
                $array = $first . ($url ? '/' : null);
            } else {
                $array = null;
            }
        }

        $result = $array === true ? '' : Strings::join($array, $url ? '/' : DS);

        return $result || $result === '' ? $result . ($url ? '/' : DS) : null;
    }

    /**
     * Очищает слеши в начале и конце пути
     *
     * @param [type] $path
     * @return void
     */
    public static function clearSlashes($path)
    {
        return preg_replace('/^[\\\\\/]*(.*?)[\\\\\/]*$/ui', '$1', $path);
    }

    /**
     * Очищает двойные слеши в пути
     *
     * @param [type] $path
     * @return void
     */
    public static function clearDoubleSlashes($path)
    {
        return preg_replace('/([\\\\\/]){2,}/ui', '$1', $path);
    }

    /**
     * определяем, абсолютный путь или нет (относительный) по :\ и :// в начале строки
     * в unix-системах пути всегда относительные:
     * localhost/path
     * /var/server/path
     * абсолютные url без протокола не проходят проверку,
     * поэтому они должны начинаться с протокола, адреса диска или с двойного слеша
     * однако это требование равносильно и для браузеров,
     * иначе адрес будет распознан неверно
     * пример:
     * other.com (false)->site.com/other.com
     * //other.com (true)->other.com
     * http://other.com (true)->other.com
     *
     * @param [type] $path
     * @return void
     */
    public static function absolute($path)
    {
        return preg_match('/^(\/|\\\\){2}|\:(\/|\\\\)/u', (string) Strings::get($path, 0, 8));
    }

    /**
     * Парсит строку get-запроса в массив данных
     *
     * @param [type] $string
     * @return void
     */
    public static function querySplit($string)
    {
        $query = [];
        $string = Strings::after(Prepare::urldecode($string), '?');
        Objects::each(Strings::split($string, '&'), function ($i) use (&$query) {
            $a = Strings::split($i, '=');
            $k = Strings::replace($a[0], ['[', ']'], [':', '']);
            $map = Strings::split($k, ':');
            $query = Objects::inject($query, $map, $a[1]);
        });
        return $query;
    }

    /**
     * Составляет строку get-запроса из массива данных
     *
     * @param [type] $array
     * @return void
     */
    public static function queryJoin($array)
    {
        if (is_array($array)) {
            Objects::recurse($array, function ($i) {
                return Prepare::urlencode($i);
            });
            return '?' . http_build_query($array);
        }
        return;
    }

    /**
     * Составляет rest строку из массива данных
     *
     * @param [type] $array
     * @return void
     */
    public static function restJoin($array)
    {
        if (is_array($array)) {
            Objects::recurse($array, function ($i) {
                return Prepare::urlencode($i);
            });
            return Strings::replace(
                http_build_query($array),
                ['%5B', '%5D', '&', '='],
                [':', '', '/', '/']
            ) . '/';
        }
        return;
    }
}
