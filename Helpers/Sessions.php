<?php

namespace is\Helpers;

class Sessions
{
    /**
     * Функция регистрации куки
     * на входе нужно указать имя куки
     * второе значение - присвоить это значение куки
     *
     * @param [type] $name
     * @param [type] $set
     * @return void
     */
    public static function setCookie($name, $set)
    {
        setcookie($name, $set, 0, '/');
        $_COOKIE[$name] = $set;
    }

    /**
     * Функция проверки куки
     * если задано то возвращает значение, если не задано, возвращает null
     * если ключ пустой, то возвращает весь массив кук
     *
     * @param [type] $name
     * @return void
     */
    public static function getCookie($name = null)
    {
        return !$name ? $_COOKIE : (!empty($_COOKIE[$name]) ? $_COOKIE[$name] : null);
    }

    /**
     * Функция удаления куки
     * на входе нужно указать имя куки
     * если вместо имени передан массив, будут удалены все указанные в нем куки
     *
     * @param [type] $name
     * @return void
     */
    public static function unCookie($name)
    {
        if (is_array($name)) {
            foreach ($name as $item) {
                setcookie($item, '', time() - 3600, '/');
                unset($_COOKIE[$item]);
            }
            unset($item);
        } else {
            setcookie($name, '', time() - 3600, '/');
            unset($_COOKIE[$name]);
        }
    }

    /**
     * Функция регистрации записи в сессионном хранилище
     * на входе нужно указать имя записи
     * второе значение - присвоить это значение записи
     *
     * @param [type] $name
     * @param [type] $set
     * @return void
     */
    public static function setSession($name, $set)
    {
        $_SESSION[$name] = $set;
    }

    /**
     * Функция проверки записи в сессионном хранилище
     * если задано то возвращает значение, если не задано, возвращает null
     * если ключ пустой, то возвращает весь массив записей
     *
     * @param [type] $name
     * @return void
     */
    public static function getSession($name = null)
    {
        return !$name ? $_SESSION : (!empty($_SESSION[$name]) ? $_SESSION[$name] : null);
    }

    /**
     * Функция удаления записи в сессионном хранилище
     * на входе нужно указать имя записи
     * если вместо имени передан массив, будут удалены все указанные в нем записи
     *
     * @param [type] $name
     * @return void
     */
    public static function unSession($name)
    {
        if (is_array($name)) {
            foreach ($name as $item) {
                unset($_SESSION[$item]);
            }
            unset($item);
        } else {
            unset($_SESSION[$name]);
        }
    }

    /**
     * Функция перезагружает страницу
     *
     * на входе можно указать:
     *   url-адрес (относительный)
     *   код ответа (заголовок будет подставлен автоматически)
     *   массив данных, которые будут добавлены в заголовок
     *
     * @param string $path
     * @param [type] $code
     * @param [type] $data
     * @return void
     */
    public static function reload($path = '/', $code = null, $data = null)
    {
        if (headers_sent()) {
            return;
        }

        self::setHeader($data);
        self::setHeaderCode($code);

        header('Location: ' . $path);

        exit;
    }

    /**
     * Функция задает заголовки на странице
     * на входе можно указать массив заголовков
     *
     * @param [type] $data
     * @return void
     */
    public static function setHeader($data = null)
    {
        if (headers_sent()) {
            return;
        }

        //echo print_r($data, 1);

        if (!empty($data) && is_array($data)) {
            foreach ($data as $key => $item) {
                //echo $key . ': ' . $item . '<br>';
                header($key . ': ' . $item);
            }
            unset($key, $item);
        }
    }

    /**
     * Функция задает код состояния
     * на входе можно указать код состояния
     *
     * @param [type] $code
     * @return void
     */
    public static function setHeaderCode($code)
    {
        if (headers_sent()) {
            return;
        }

        if (!empty($code)) {
            $status = self::code($code);
            header(System::server('protocol') . ' ' . $code . ' ' . $status, true, (int) $code);
        }
    }

    /**
     * Функция возвращает статус ответа для переданного кода
     *
     * @param integer $code
     * @return void
     */
    public static function code($code = 200)
    {
        $type = null;

        switch ((int) $code) {
            // 1xx informational response
            case '100':
                $type = 'Continue';
                break;
            case '101':
                $type = 'Switching Protocols';
                break;
            case '102':
                $type = 'Processing';
                break;
                // WebDAV; RFC 2518
            case '103':
                $type = 'Early Hints';
                break;
                // RFC 8297

             // 2xx success
            case '200':
                $type = 'OK';
                break;
            case '201':
                $type = 'Created';
                break;
            case '202':
                $type = 'Accepted';
                break;
            case '203':
                $type = 'Non-Authoritative Information';
                break;
                // since HTTP/1.1
            case '204':
                $type = 'No Content';
                break;
            case '205':
                $type = 'Reset Content';
                break;
            case '206':
                $type = 'Partial Content';
                break;
                // RFC 7233
            case '207':
                $type = 'Multi-Status';
                break;
                // WebDAV; RFC 4918
            case '208':
                $type = 'Already Reported';
                break;
                // WebDAV; RFC 5842
            case '226':
                $type = 'IM Used';
                break;
                // RFC 3229

            // 3xx redirection
            case '300':
                $type = 'Multiple Choices';
                break;
            case '301':
                $type = 'Moved Permanently';
                break;
            case '302':
                $type = 'Found';
                break;
                // Previously "Moved temporarily"
            case '303':
                $type = 'See Other';
                break;
                // since HTTP/1.1
            case '304':
                $type = 'Not Modified';
                break;
                // RFC 7232
            case '305':
                $type = 'Use Proxy';
                break;
                // since HTTP/1.1
            case '306':
                $type = 'Switch Proxy';
                break;
            case '307':
                $type = 'Temporary Redirect';
                break;
                // since HTTP/1.1
            case '308':
                $type = 'Permanent Redirect';
                break;
                // RFC 7538

            // 4xx client errors
            case '400':
                $type = 'Bad Request';
                break;
            case '401':
                $type = 'Unauthorized';
                break;
                // RFC 7235
            case '402':
                $type = 'Payment Required';
                break;
            case '403':
                $type = 'Forbidden';
                break;
            case '404':
                $type = 'Not Found';
                break;
            case '405':
                $type = 'Method Not Allowed';
                break;
            case '406':
                $type = 'Not Acceptable';
                break;
            case '407':
                $type = 'Proxy Authentication Required';
                break;
                // RFC 7235
            case '408':
                $type = 'Request Timeout';
                break;
            case '409':
                $type = 'Conflict';
                break;
            case '410':
                $type = 'Gone';
                break;
            case '411':
                $type = 'Length Required';
                break;
            case '412':
                $type = 'Precondition Failed';
                break;
                // RFC 7232
            case '413':
                $type = 'Payload Too Large';
                break;
                // RFC 7231
            case '414':
                $type = 'URI Too Long';
                break;
                // RFC 7231
            case '415':
                $type = 'Unsupported Media Type';
                break;
                // RFC 7231
            case '416':
                $type = 'Range Not Satisfiable';
                break;
                // RFC 7233
            case '417':
                $type = 'Expectation Failed';
                break;
            case '418':
                $type = 'I\'m a teapot';
                break;
                // RFC 2324, RFC 7168
            case '421':
                $type = 'Misdirected Request';
                break;
                // RFC 7540
            case '422':
                $type = 'Unprocessable Entity';
                break;
                // WebDAV; RFC 4918
            case '423':
                $type = 'Locked';
                break;
                // WebDAV; RFC 4918
            case '424':
                $type = 'Failed Dependency ';
                break;
                // ebDAV; RFC 4918
            case '425':
                $type = 'Too Early';
                break;
                // RFC 8470
            case '426':
                $type = 'Upgrade Required';
                break;
            case '428':
                $type = 'Precondition Required';
                break;
                // RFC 6585
            case '429':
                $type = 'Too Many Requests';
                break;
                // RFC 6585
            case '431':
                $type = 'Request Header Fields Too Large';
                break;
                // RFC 6585
            case '451':
                $type = 'Unavailable For Legal Reasons';
                break;
                // RFC 7725

            // 5xx server errors
            case '500':
                $type = 'Internal Server Error';
                break;
            case '501':
                $type = 'Not Implemented';
                break;
            case '502':
                $type = 'Bad Gateway';
                break;
            case '503':
                $type = 'Service Unavailable';
                break;
            case '504':
                $type = 'Gateway Timeout';
                break;
            case '505':
                $type = 'HTTP Version Not Supported';
                break;
            case '506':
                $type = 'Variant Also Negotiates';
                break;
                // RFC 2295
            case '507':
                $type = 'Insufficient Storage';
                break;
                // WebDAV; RFC 4918
            case '508':
                $type = 'Loop Detected';
                break;
                // WebDAV; RFC 5842
            case '510':
                $type = 'Not Extended';
                break;
                // RFC 2774
            case '511':
                $type = 'Network Authentication Required';
                break;
                // RFC 6585
        }

        return $type;
    }

    /**
     * Функция записывает данные в куки
     * и задает хэш данных в сессию
     *
     * @param [type] $name
     * @param [type] $data
     * @return void
     */
    public static function setHash($name, $data)
    {
        $data = json_encode($data);

        $_SESSION[$name] = md5($data);
        self::setCookie($name, $data);
    }

    /**
     * Функция читает хэш данных из кук
     * сверяет его с хэшем данных в сессии
     * и если все в порядке, возвращает данные
     * в противном случае возвращает null
     *
     * @param [type] $name
     * @return void
     */
    public static function getHash($name)
    {
        $data = self::getCookie($name);

        return !$data && $_SESSION[$name] === md5($data) ? json_decode($data, true) : null;
    }
}
