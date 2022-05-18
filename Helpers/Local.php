<?php

namespace is\Helpers;

use ZipArchive;

class Local
{
    /**
     * Новая функция, которая строит карту файлов и папок, как в структуре
     * на входе нужно указать путь к папке $path и массив параметров
     * по своим параметрам и действиям копирует search, но дает другой результат
     *
     * @param [type] $path
     * @param array $parameters
     * @param [type] $basepath
     * @return void
     */
    public static function map($path, $parameters = [], $basepath = null)
    {
        if (!file_exists($path) || !is_dir($path)) {
            return false;
        }

        $path = str_replace(['/', '\\'], DS, $path);
        if (substr($path, -1) !== DS) {
            $path .= DS;
        }

        $scan = scandir($path);

        if (!System::typeIterable($scan)) {
            return false;
        }

        $scan = Objects::sort($scan);

        // настраиваем параметры

        $parameters['extension'] = Parser::fromString($parameters['extension']);
        $parameters['skip'] = Parser::fromString($parameters['skip']);

        // разбираем список

        $map = [];

        foreach ((array) $scan as $item) {
            // здесь мы определяем, пропускать файл или нет
            // раньше еше была строка
            // $disable = $parameters['nodisable'] ? null : Strings::first($item) === '!';
            // и условия if ... !$disable ...
            // но зачем эти сложности?

            if (
                $item === '.'
                || $item === '..'
                || (
                    !$parameters['nodisable']
                    && Strings::first($item) === '!'
                )
            ) {
                continue;
            }

            // задаем базовый путь
            $pathto = $path . $item;
            // определяем, папка это или файл
            $isdir = is_dir($pathto);

            // еще одни параметры пропуска
            if (
                (
                    $isdir
                    && $parameters['return'] === 'files'
                )
                || (
                    !$isdir
                    && $parameters['return'] === 'folders'
                )
            ) {
                continue;
            }

            $info = pathinfo($pathto);

            // задаем базовый ресурс
            $i = [
                'fullpath' => $pathto . ($isdir ? DS : null),
                'file' => $isdir ? null : $info['filename'],
                'extension' => $isdir ? null : $info['extension']
            ];

            // и снова параметры пропуска
            // по расширению
            if (
                System::typeIterable($parameters['extension'])
                && $i['extension']
                && !Objects::match($parameters['extension'], $i['extension'])
            ) {
                continue;
            }

            $key = $isdir ? $item : $i['file'];

            if (
                $parameters['subfolders']
                && $isdir
            ) {
                $result = self::map($i['fullpath'], $parameters);
            } elseif (!$map[$key]) {
                $result = true;
            }

            $map[$key] = $result;
        }
        unset($scan, $item);

        return $map;
    }

    /**
     * Функция получения списка файлов или папок с определенным расширением
     * на входе нужно указать путь к папке $name и массив параметров
     * return
     *   files - только файлы
     *   folders - только папки
     *   пропуск - и то и другое
     * type/extension
     *   для файлов, вернуть только файлы указанного типа
     * info
     *   любой ключ - часть по указанному ключу
     *   пропуск - полную инфу
     * subfolders
     *   true/false включать в список подпапки
     * nodisable
     *   true/false включать в список заблокированные элементы - файлы и папки
     * merge
     *   true/false смешать список - папки, затем подпапки, затем файлы, затем подфайлы
     * skip * пока не реализован
     *   set - строка исключения
     *   folders
     *     true/false разрешить исключать папки
     *   files
     *     true/false разрешить исключать файлы
     * mask * пока не реализован
     *   set - строка совпадения
     *   in - ключ info, где будет использована
     * третий параметр используется только для служебных целей,
     * т.к. функция рекурсивная при вызове параметра 'subfolders'
     * функция возвращает готовый массив
     *
     * @param [type] $path
     * @param array $parameters
     * @param [type] $basepath
     * @return void
     */
    public static function list($path, $parameters = [], $basepath = null)
    {
        // now dirconnect and fileconnect is localList
        //fileconnect($dir, $ext = false)
        //localList($path, ['return' => 'files'/*, 'type' => $ext*/])
        // $ext - либо массив значений, либо строка '_:_:_'
        //dirconnect($dir, $ext = false)
        //localList($path, ['return' => 'folders'/*, 'skip' => $ext*/])
        // $ext - либо массив значений, либо строка '_:_:_'

        if (!file_exists($path) || !is_dir($path)) {
            return false;
        }

        $path = str_replace(['/', '\\'], DS, $path);
        if (substr($path, -1) !== DS) {
            $path .= DS;
        }

        $scan = scandir($path);

        if (!System::typeIterable($scan)) {
            return false;
        }

        $scan = Objects::sort($scan);

        // настраиваем параметры

        $parameters = Objects::createByIndex(
            ['return', 'extension', 'info', 'subfolders', 'nodisable', 'merge', 'skip'],
            $parameters
        );

        $parameters['extension'] = Parser::fromString($parameters['extension']);
        $parameters['skip'] = Parser::fromString($parameters['skip']);

        // разбираем список

        $list = [
            'folders' => [],
            'files' => [],
            'sub' => [
                'folders' => [],
                'files' => []
            ]
        ];

        foreach ((array) $scan as $key => $item) {
            if ($item !== '.' && $item !== '..') {
                $pathto = $path . $item;
                $isdir = is_dir($pathto);

                $i = [
                    'fullpath' => $pathto . ($isdir ? DS : null),
                    'name' => $item,
                    'type' => ($isdir ? 'folder' : 'file'),
                    'path' => $basepath ? Strings::get(
                        $pathto,
                        Strings::len($basepath),
                        Strings::len($item),
                        true
                    ) : null,
                    'file' => null,
                    'extension' => null
                ];

                $disable = $parameters['nodisable'] ? null : Strings::first($item) === '!';

                if (
                    !$disable
                    && !$isdir
                    && $parameters['return'] !== 'folders'
                ) {
                    $info = Objects::createByIndex(
                        ['filename', 'extension'],
                        pathinfo($pathto)
                    );
                    $i['file'] = $info['filename'];
                    $i['extension'] = $info['extension'];

                    if (
                        !$parameters['extension']
                        || (
                            $parameters['extension']
                            && $i['extension']
                            && Objects::match($parameters['extension'], $i['extension'])
                        )
                    ) {
                        $list['files'][] = $parameters['info'] ? $i[$parameters['info']] : $i;
                    }
                }

                if (!$disable && $isdir) {
                    if ($parameters['return'] !== 'files') {
                        $list['folders'][] = $parameters['info'] ? $i[$parameters['info']] : $i;
                    }

                    if ($parameters['subfolders']) {
                        $sub = self::list($pathto, $parameters, $basepath ? $basepath : $path);

                        $list['sub'] = array_merge_recursive(
                            isset($list['sub']) ? $list['sub'] : [],
                            $sub ? $sub : []
                        );

                        if ($basepath) {
                            $list['folders'] = array_merge_recursive(
                                isset($list['folders']) ? $list['folders'] : [],
                                $list['sub']['folders'] ? $list['sub']['folders'] : []
                            );
                            $list['files'] = array_merge_recursive(
                                isset($list['files']) ? $list['files'] : [],
                                $list['sub']['files'] ? $list['sub']['files'] : []
                            );
                            unset($list['sub']);
                        }
                    }
                }

                unset($i);
            }
        }

        //$list['folders'] = array_merge_recursive(
        //  $list['folders'],
        //  $list['sub']['folders'],
        //  $list['files'],
        //  $list['sub']['files']
        //);

        if (!$basepath && $parameters['merge']) {
            $list = array_merge($list['folders'], $list['sub']['folders'], $list['files'], $list['sub']['files']);
        }
        //$list = array_merge($list['folders'], $list['files']);
        //if (!$basepath) { echo '<pre>'.print_r($list, 1).'</pre><br>'; }

        return $list;
    }

    /**
     * Функция копирует файл
     *
     * @param [type] $from
     * @param [type] $to
     * @return void
     */
    public static function copyFile($from, $to)
    {
        return copy($from, $to);
    }

    /**
     * Функция переименовывает или перемещает файл
     *
     * @param [type] $from
     * @param [type] $to
     * @return void
     */
    public static function renameFile($from, $to)
    {
        return rename($from, $to);
    }

    /**
     * Функция создает файл
     *
     * @param [type] $target
     * @return void
     */
    public static function createFile($target)
    {
        $pos = (int) Strings::find($target, DS, 'r') + 1;
        $folder = (string) Strings::get($target, 0, $pos);
        $file = Strings::get($target, $pos);

        if (!file_exists($folder)) {
            self::createFolder($folder);
        }

        file_put_contents($target, null, LOCK_EX);
    }

    /**
     * Функция создает папку
     *
     * @param [type] $target
     * @return void
     */
    public static function createFolder($target)
    {
        $target = Paths::clearSlashes($target);
        $split = Strings::split($target, '\\' . DS);

        $result = null;

        foreach ((array) $split as $item) {
            $result .= $item . DS;
            if (!file_exists($result)) {
                mkdir($result);
            }
        }
        unset($item);
    }

    /**
     * Функция удаляет папку вместе со всем содержимым
     *
     * @param [type] $target
     * @return void
     */
    public static function deleteFolder($target)
    {
        self::eraseFolder($target);
        chmod($target, 0755);
        return rmdir($target);
    }

    /**
     * Функция очищает содержимое папки
     *
     * @param [type] $target
     * @return void
     */
    public static function eraseFolder($target)
    {
        $list = self::list($target, ['subfolders' => true, 'merge' => true]);
        $list = Objects::reverse($list);

        foreach ((array) $list as $item) {
            if ($item['type'] === 'folder') {
                chmod($item['fullpath'], 0755);
                rmdir($item['fullpath']);
            } else {
                self::deleteFile($item['fullpath']);
            }
        }
        unset($item);
    }

    /**
     * Функция проверяет существование файла $target
     * на входе нужно указать полный путь к файлу с названием и расширением
     *
     * @param [type] $target
     * @return void
     */
    public static function matchFolder($target)
    {
        return is_dir($target);
    }

    /**
     * Функция проверяет существование файла $target
     * на входе нужно указать полный путь к файлу с названием и расширением
     *
     * @param [type] $target
     * @return void
     */
    public static function matchFile($target)
    {
        return is_file($target);
    }

    /**
     * Новая функция, которая проверяет
     * существование файла в файловой системе по url
     * Сначала она преобразует url в абсолютный путь
     * затем проверяет наличие файла
     * второй аргумент устанавливает проверку по типу:
     * файл, папка или что угодно (по-умолчанию)
     * третий аргумент проверяет наличие файла по внешнему url
     * через проверку ответа 200
     *
     * @param [type] $target
     * @param [type] $type
     * @param [type] $external
     * @return void
     */
    public static function matchUrl($target, $type = null, $external = null)
    {
        if (!$target) {
            return;
        }

        $int = null;

        $info = Objects::createByIndex(
            ['host', 'path'],
            Paths::parseUrl($target)
        );

        $host = System::server('host');

        if ($info['host'] && $info['host'] === $host) {
            $path = $info['path'];
            $int = true;
        } else {
            $path = $target;
        }

        if (Strings::find($path, $host, 0)) {
            $len = Strings::len($host);
            $path = Strings::get($path, $len);
            $int = true;
        } elseif (!$int) {
            $int = !Paths::absolute($target);
        }

        if (!$int && !$external) {
            return;
        }

        if ($int) {
            $path = realpath(DI . Paths::toReal($path));

            if (!$path) {
                return;
            }

            $is_file = self::matchFile($path);
            $is_dir = self::matchFolder($path);

            return (
                (
                    $type === 'file'
                    && !$is_file
                )
                || (
                    $type === 'folder'
                    && !$is_dir
                )
                || (
                    !$is_file
                    && !$is_dir
                )
            )
                ? null
                : filemtime($path);
        }

        if ($external && Strings::get($path, 0, 4) === 'http') {
            // this for !int and http/https requests

            $headers = get_headers($path);
            $result = null;
            Objects::each($headers, function ($item) use (&$result) {
                if ($item === 'HTTP/1.1 200 OK') {
                    $result = true;
                }
            });
            return $result;
        }
    }

    /**
     * Функция открывает файл $target
     * на входе нужно указать полный путь к файлу с названием и расширением
     * вывод через функцию file_get_contents по сравнению с fopen+fgets+fclose
     * оказывается быстрее при том же потреблении памяти, т.к. использует memory mapping
     * функция вернет строку
     *
     * @param [type] $target
     * @return void
     */
    public static function readFile($target)
    {
        if (!file_exists($target)) {
            return null;
        }

        return file_get_contents($target);
    }

    /**
     * Функция открывает файл $target и читает его построчно
     * на входе нужно указать полный путь к файлу с названием и расширением
     * вторым аргументом можно задать разделитель строк, по-умолчанию - без разделителя
     * отличие от предыдущей функции в том, что эта работает построчно
     * функция возвращает строку
     *
     * @param [type] $target
     * @param [type] $separator
     * @return void
     */
    public static function readFileLine($target, $separator = null)
    {
        if (!file_exists($target)) {
            return null;
        }

        $result = null;

        $handle = fopen($target, "r");
        while (!feof($handle)) {
            $result .= fgets($handle) . $separator;
        }
        fclose($handle);

        return $result;
    }

    /**
     * Функция открывает файл $target и читает его построчно
     * на входе нужно указать полный путь к файлу с названием и расширением
     * отличие от предыдущей функции в том, что эта возвращает массив строк
     *
     * @param [type] $target
     * @return void
     */
    public static function readFileArray($target)
    {
        if (!file_exists($target)) {
            return null;
        }

        $lines = [];

        $handle = fopen($target, "r");
        while (!feof($handle)) {
            $lines[] = fgets($handle);
        }
        fclose($handle);

        return $lines;
    }

    /**
     * Функция открывает файл $target и читает его построчно
     * на входе нужно указать полный путь к файлу с названием и расширением
     * отличие от предыдущих функций в том, что эта действует через генератор
     * это значит, что она позволяет распределять ресурсы при большой нагрузке
     * и потребляет меньше оперативной памяти - размером ровно на одну строку
     * результат этой функции нужно оборачивать в итератор, например:
     * foreach (Local::readFileGenerator($path) as $index => $line) {
     *   ...
     * }
     * функция возвращает текущую строку итерации
     *
     * @param [type] $target
     * @return void
     */
    public static function readFileGenerator($target)
    {
        $handle = fopen($target, "r");
        while (!feof($handle)) {
            yield fgets($handle);
        }
        fclose($handle);
    }

    /**
     * Функция сохраняет данные $data в файл $target
     * на входе нужно указать полный путь к файлу с названием и расширением
     * второй параметр - данные для записи
     * последний параметр задает режим
     *   null/false/по-умолчанию - запись в новый файл
     *   replace - замена файла
     *   append - дозапись в конец файла
     * здесь вывод через функцию file_put_contents по сравнению с fopen+fwrite+fclose
     * функция вернет true в случае успешного выполнения
     *
     * @param [type] $target
     * @param [type] $data
     * @param [type] $mode
     * @return void
     */
    public static function writeFile($target, $data = null, $mode = null)
    {
        if (is_writable($target)) {
            if (!$mode) {
                return false;
            } elseif ($mode === 'replace') {
                //self::deleteFile($target);
                // erase вместо delete выбран по той причине, что delete изменяет ctime файла
                // а этого по условиям действия функции не нужно
                self::eraseFile($target);
            }
        }

        if ($mode === 'append') {
            return file_put_contents($target, $data, FILE_APPEND | LOCK_EX);
        } else {
            return file_put_contents($target, $data, LOCK_EX);
        }
    }

    /**
     * Функция открывает файл $target и записывает его построчно
     *
     * на входе нужно указать полный путь к файлу с названием и расширением
     * отличие от предыдущей функции в том, что эта
     * может принимать на вход как массив, так и обычные данные
     * и записывает их построчно
     *
     * @param [type] $target
     * @param [type] $data
     * @param [type] $mode
     * @param [type] $separator
     * @return void
     */
    public static function writeFileLine($target, $data = null, $mode = null, $separator = PHP_EOL)
    {
        $handle = fopen($target, $mode === 'append' ? "c" : "w");
        fseek($handle, 0, SEEK_END);
        if (System::typeOf($data, 'iterable')) {
            foreach ($data as $item) {
                fwrite($handle, $item . $separator);
            }
            unset($item);
        } else {
            fwrite($handle, $data);
        }

        fclose($handle);
    }

    /**
     * Функция открывает файл $target и записывает его построчно
     * на входе нужно указать полный путь к файлу с названием и расширением
     *
     * отличие от предыдущих функций в том, что эта действует через генератор
     * это значит, что она позволяет распределять ресурсы при большой нагрузке
     * и потребляет меньше оперативной памяти - размером ровно на одну строку
     *
     * передавать данные нужно через втроенный системный метод send(), например:
     * $file = Local::writeFileGenerator($path);
     * foreach ($data as $index => $line) {
     *   ...
     *   $file->send($line);
     * }
     *
     * Функция прекращает работу после передачи пустого значения
     *
     * @param [type] $target
     * @param [type] $mode
     * @param [type] $separator
     * @return void
     */
    public static function writeFileGenerator($target, $mode = null, $separator = PHP_EOL)
    {
        $handle = fopen($target, $mode === 'append' ? "c" : "w");
        fseek($handle, 0, SEEK_END);

        $c = true;

        while ($c) {
            $data = yield;
            if (
                !System::set($data)
            ) {
                $c = null;
            } else {
                fwrite($handle, $data . $separator);
            }
        }

        fclose($handle);
        yield false;
    }

    /**
     * Функция удаляет файл $target
     * на входе нужно указать полный путь к файлу с названием и расширением
     *
     * @param [type] $target
     * @return void
     */
    public static function deleteFile($target)
    {
        if (!file_exists($target)) {
            return null;
        }

        chmod($target, 0644);
        return unlink($target);
    }

    /**
     * Функция очищает содержимое файла $target, оставляя сам файл
     * на входе нужно указать полный путь к файлу с названием и расширением
     *
     * @param [type] $target
     * @return void
     */
    public static function eraseFile($target)
    {
        fclose(fopen($target, 'w'));
    }

    /**
     * Простая функция, которая проверяет указанный файл
     * и возвращает путь к нему вместе с параметром версии
     *
     * Первым параметром задается путь
     * Вторым параметром - системная папка
     * Третий, необязательный параметр - префикс, который назначается принудительно
     *  - если 'true', то устанавливается от времени изменения файла
     *  - если пустой, то не устанавливается
     * Четвертый, необязательный параметр - минимизированный вариант файла
     * Пятый, необязательный параметр - тип возврата
     *
     * Путь читается в формате '/', '\' или ':'
     * Например, 'path\to\file.ext' вернет 'path/to/file.ext?mtime'
     *
     * @param [type] $path
     * @param [type] $folder
     * @param boolean $prefix
     * @param [type] $variant
     * @param [type] $return
     * @return void
     */
    public static function link($path, $folder, $prefix = true, $variant = null, $return = null)
    {
        $path = Prepare::clear($path);
        $path = (string) Prepare::urldecode($path);

        if (!empty($variant)) {
            $point = strrpos($path, '.');
            $pathv = substr($path, 0, $point) . '.' . $variant . substr($path, $point);
            $filev = constant('PATH_' . strtoupper($folder)) . str_replace(['/', '\\', ':'], DS, $pathv);
            unset($point);
        } else {
            $pathv = null;
            $filev = null;
        }

        if (
            !empty($pathv)
            && !empty($filev)
            && file_exists($filev)
        ) {
            $file = $filev;
            $path = $pathv;
        } else {
            $file = constant('PATH_' . strtoupper($folder)) . str_replace(['/', '\\', ':'], DS, $path);
            if (!file_exists($file)) {
                return null;
            }
        }

        return constant('URL_' . strtoupper($folder))
            . str_replace(['/', '\\', ':'], '/', $path)
            . (
                !empty($prefix)
                ? '?' . ($prefix === true ? filemtime($file) : $prefix)
                : null
            );
    }

    /**
     * Функция, позволяющая скопировать папку со всем содержимым
     * в другую папку на сервере
     *
     * первый параметр - адрес исходной папки
     * второй параметр - адрес, куда копировать (это может быть несуществующая папка)
     * третий параметр - перезапись файлов, если они имеются
     *
     * @param [type] $from
     * @param [type] $to
     * @param boolean $rewrite
     * @return void
     */
    public static function copy($from, $to, $rewrite = true)
    {
        if (!file_exists($from)) {
            return false;
        }

        if (is_dir($from)) {
            @mkdir($to);
            $d = dir($from);

            while (($entry = $d->read()) !== false) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                self::copy($from . DS . $entry, $to . DS . $entry, $rewrite);
            }

            $d->close();
        } elseif (!file_exists($to) || $rewrite) {
            copy($from, $to);
        }
    }

    /**
     * Функция, позволяющая сохранить файл на сервере,
     * содержимое которого прочитано из url
     *
     * первым параметром передается адрес файла для сохранения на сервере
     * второй параметр - адрес ссылки
     * третий параметр определяет поведение при условии, если файл с таким именем уже существует:
     *   false - по-умолчанию, ничего не делать, пропускать работу функции и возвращать false
     *   true - сперва удалять файл, а затем записывать его заново
     *
     * @param [type] $filename
     * @param [type] $url
     * @param boolean $delete
     * @return void
     */
    public static function saveFromUrl($filename, $url, $delete = false)
    {
        if (file_exists($filename)) {
            if (!$delete) {
                return false;
            } else {
                self::deleteFile($filename);
            }
        }

        $file = file_get_contents($url);

        if (empty($file)) {
            return false;
        } elseif (file_put_contents($filename, $file)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Функция, позволяющая загружать файл с сервера,
     * содержимое которого прочитано из url
     *
     * первым параметром передается url файла
     * второй параметр - метод
     *
     * третий параметр служебный, служит для предотвращения более 1 редиректа
     *
     * @param [type] $url
     * @param boolean $method
     * @param integer $redirect
     * @return void
     */
    public static function openUrl($url, $method = false, $redirect = 0)
    {
        $target = null;

        if (!$method || $method === true) {
            $target = file_get_contents($url);
        } elseif ($method === 'curl' && extension_loaded('curl')) {
            $init = curl_init();
            curl_setopt($init, CURLOPT_URL, $url);
            curl_setopt($init, CURLOPT_HTTPGET, true);
            curl_setopt($init, CURLOPT_USERAGENT, System::server('agent'));
            curl_setopt($init, CURLOPT_RETURNTRANSFER, true);
            //curl_setopt($init, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            //curl_setopt($init, CURLOPT_HEADER, true);

            $target = curl_exec($init);
            //$errors = curl_error($init);

            // вывод ошибок пока закомментирован, т.к. он не отлажен

            // код ниже осуществляет проверку заголовков и статусов
            // если, например, статус не равен 200, то возвращает false
            // а если задан редирект, то переходит по нему

            // начало кода
            $info = [
                'headers' => curl_getinfo($init),
                'code' => null,
                'redirect' => null
            ];

            if (!empty($info['headers']['http_code'])) {
                $info['code'] = $info['headers']['http_code'];
            }
            if (!empty($info['headers']['redirect_url'])) {
                $info['redirect'] = $info['headers']['redirect_url'];
            }

            unset($info['headers']);

            if ($info['redirect']) {
                if ($redirect > 1) {
                    $target = false;
                } else {
                    $target = self::openUrl($info['redirect'], $method, $redirect++);
                }
            } elseif ($info['code'] != '200') {
                $target = false;
            }
            // конец кода

            //if (DEFAULT_MODE === 'develop' && !empty($errors)) {
            //    $target = print_r($errors, true);
            //}

            curl_close($init);
        } elseif ($method === 'fsock') {
            // Устанавливаем соединение
            $init = fsockopen($url, -1, $errno, $errstr, 30);

            // Формируем HTTP-заголовки для передачи его серверу
            $header = 'GET ' . $url . ' ' . System::server('protocol') . "\r\n";
            $header .= 'User-Agent: ' . System::server('agent') . "\r\n";
            $header .= "Connection: Close\r\n\r\n";

            // Отправляем HTTP-запрос серверу
            fwrite($init, $header);

            // Получаем ответ
            while (!feof($init)) {
                $target .= fgets($init, 1024);
            }

            // Закрываем соединение
            fclose($init);
        }

        return $target;
    }

    /**
     * Функция, позволяющая запросить какой-либо url
     *
     * первым параметром передается url
     * второй параметр - данные
     * третий параметр - метод (любой метод обращается к вызову через curl)
     * на данный момент поддерживается только post и только через curl
     * также нужно быть внимательным, чтобы отправлять запрос с абсолютной ссылкой, а не относительной
     *
     * четвертый параметр служебный, служит для предотвращения более 1 редиректа
     * на данный момент редирект убран из кода
     *
     * @param [type] $url
     * @param boolean $data
     * @param boolean $method
     * @param integer $redirect
     * @return void
     */
    public static function requestUrl($url, $data = false, $method = false, $redirect = 0)
    {
        $target = null;

        if (!$method || $method === true) {
            $target = file_get_contents($url);
        } elseif (is_string($method) && extension_loaded('curl')) {
            $init = curl_init();

            curl_setopt($init, CURLOPT_URL, $url);

            if (strtolower($method) === 'post') {
                curl_setopt($init, CURLOPT_POST, true);
                curl_setopt($init, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($init, CURLOPT_HTTPGET, true);
                //$url .= ''
            }

            curl_setopt($init, CURLOPT_USERAGENT, System::server('agent'));
            curl_setopt($init, CURLOPT_RETURNTRANSFER, true);

            $target = curl_exec($init);

            // код ниже осуществляет проверку заголовков и статусов
            // если, например, статус не равен 200, то возвращает false
            // а если задан редирект, то переходит по нему

            // начало кода
            $info = [
                'headers' => curl_getinfo($init),
                'code' => null
            ];

            if (!empty($info['headers']['http_code'])) {
                $info['code'] = $info['headers']['http_code'];
            }

            unset($info['headers']);

            if ($info['code'] != '200') {
                $target = false;
            }
            // конец кода

            curl_close($init);
        }

        return $target;
    }

    /**
     * Функция, позволяющая распаковать файл, хранящийся на сервере
     *
     * первым параметром передается имя и адрес файла на сервере
     * вторым - путь для распаковки
     * третий параметр определяет поведение после успешной распаковки:
     *   false - оставить исходный файл архива
     *   true - удалить его (по-умолчанию)
     *
     * @param [type] $filename
     * @param [type] $path
     * @param boolean $delete
     * @return void
     */
    public static function unzip($filename, $path, $delete = true)
    {
        if (
            !extension_loaded('zip')
            || !file_exists($filename)
        ) {
            return false;
        }

        $zip = new ZipArchive();

        $res = $zip->open($filename);

        if ($res === true) {
            $zip->extractTo($path);
            $zip->close();

            if ($delete === true) {
                if (!self::deleteFile($filename)) {
                    return false;
                }
            }

            return true;
        } else {
            return false;
        }
    }
}
