<?php

namespace Machine\Http;

class Request
{
    protected $baseUrl;
    protected $method;
    protected $host;
    protected $scheme;
    protected $httpPort;
    protected $httpsPort;
    protected $queryString;

    protected $requestUri;
    protected $path;

    public $query;
    public $request;
    public $server;
    public $files;
    public $cookies;
    public $headers;

    public function __construct()
    {
        $this->initialize($_SERVER, $_GET, $_POST, $_FILES, $_COOKIE);
    }

    public function initialize(array $server, array $query, array $request, array $files, array $cookies)
    {
        $this->server = $server;
        $this->headers = $this->getHeaders();
        $this->query = $query;
        $this->request = $request;
        $this->files = $files;
        $this->cookies = $cookies;

        if (in_array(strtoupper($this->getServer('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'])) {
            parse_str(file_get_contents('php://input'), $this->request);
        }

        $this->setBaseUrl($this->getBaseUrl());
        $this->setMethod($this->getMethod());
        $this->setHost($this->getHost());
        $this->setScheme($this->getScheme());
        $this->setHttpPort($this->getPort());
        $this->setHttpsPort($this->getPort());
        $this->setQueryString($this->getServer('QUERY_STRING'));
    }

    public function getHeaders()
    {
        $headers = [];
        $contentHeaders = ['CONTENT_TYPE' => true, 'CONTENT_LENGTH' => true, 'CONTENT_MD5' => true];

        foreach ($this->server as $key => $value) {
            if ('HTTP_' === substr($key, 0, 5)) {
                $headers[substr($key, 5)] = $value;
            } elseif (array_key_exists($key, $contentHeaders)) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    public function getPath()
    {
        if (null === $this->path) {
            $this->path = $this->preparePath();
        }

        return $this->path;
    }

    public function getBaseUrl()
    {
        if (null === $this->baseUrl) {
            $this->baseUrl = $this->prepareBaseUrl();
        }

        return $this->baseUrl;
    }

    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function getMethod()
    {
        if (null === $this->method) {
            $this->method = strtoupper($this->getServer('REQUEST_METHOD', 'GET'));
        }

        return $this->method;
    }

    public function setMethod($method)
    {
        $this->method = strtoupper($method);

        return $this;
    }

    public function isMethod($method)
    {
        return strtoupper($method) === $this->getMethod();
    }

    public function isPost()
    {
        return $this->isMethod('POST');
    }

    public function isGet()
    {
        return $this->isMethod('GET');
    }

    public function isPut()
    {
        return $this->isMethod('PUT');
    }

    public function isPatch()
    {
        return $this->isMethod('PATCH');
    }

    public function isHead()
    {
        return $this->isMethod('HEAD');
    }

    public function isDelete()
    {
        return $this->isMethod('DELETE');
    }

    public function isOptions()
    {
        return $this->isMethod('OPTIONS');
    }

    public function isSecure()
    {
        return array_key_exists('HTTPS', $this->server) && 'on' == $this->getServer('HTTPS');
    }

    public function getHost()
    {
        if (null === $this->host) {
            if (!$this->host = $this->getHeaders('HOST')) {
                if (!$this->host = $this->getServer('SERVER_NAME')) {
                    $this->host = $this->getServer('SERVER_ADDR', '');
                }
            }
        }

        return $this->host;
    }

    public function setHost($host)
    {
        $this->host = strtolower($host);

        return $this;
    }

    public function getScheme()
    {
        if (null === $this->scheme) {
            $this->scheme = $this->prepareScheme();
        }
        return $this->scheme;
    }

    public function setScheme($scheme)
    {
        $this->scheme = strtolower($scheme);

        return $this;
    }

    public function getHttpPort()
    {
        return $this->httpPort;
    }

    public function setHttpPort($httpPort)
    {
        $this->httpPort = (int)$httpPort;

        return $this;
    }

    public function getHttpsPort()
    {
        return $this->httpsPort;
    }

    public function setHttpsPort($httpsPort)
    {
        $this->httpsPort = (int)$httpsPort;

        return $this;
    }

    public function getQueryString()
    {
        return $this->queryString;
    }

    public function setQueryString($queryString)
    {
        $this->queryString = (string)$queryString;

        return $this;
    }

    public function getScriptName()
    {
        return $this->getServer('SCRIPT_NAME');
    }

    public function getRequestUri()
    {
        if (null === $this->requestUri) {
            $this->requestUri = $this->prepareRequestUri();
        }

        return $this->requestUri;
    }

    public function getUri()
    {
        if (null != $qs = $this->getQueryString()) {
            $qs = '?' . $qs;
        }

        return $this->getScheme() . '://' . $this->getHost() . $this->getBaseUrl() . $this->getPath() . $qs;
    }

    public function getCurrentUri()
    {
        $baseUrl = $this->getBaseUrl();
        $requestUri = $this->getRequestUri();

        if (null === $requestUri) {
            return '/';
        }

        $uri = substr(rawurldecode($requestUri), strlen($baseUrl));

        if ($pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        $uri = rtrim($requestUri, '/');

        return $uri;
    }

    public function getClientIp()
    {
        $ip = $this->getServer('REMOTE_ADDR');

        $keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP'];

        foreach ($keys as $key) {
            if (isset($this->server[$key])) {
                return $this->server[$key];
            }
        }

        if ('::1' === $ip) {
            $ip = '127.0.0.1';
        }

        return $ip;
    }

    protected function prepareScheme()
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    protected function prepareBaseUrl()
    {
        $baseUrl = implode('/', array_slice(explode('/', $this->getServer('SCRIPT_NAME')), 0, -1));

        return rtrim($baseUrl, '/');
    }

    protected function prepareRequestUri()
    {
        $requestUri = $this->getServer('REQUEST_URI');

        return $requestUri;
    }

    protected function preparePath()
    {
        $baseUrl = $this->getBaseUrl();
        $requestUri = $this->getRequestUri();

        if (null === $requestUri) {
            return '/';
        }

        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        $path = substr(rawurldecode($requestUri), strlen($baseUrl));

        if (1 >= strlen($path)) {
            return $path;
        }

        return rtrim($path, '/');
    }

    public function getPort()
    {
        return $this->getServer('SERVER_PORT');
    }

    public function __toString()
    {
        return sprintf('%s %s %s', $this->getServer('SERVER_PROTOCOL'), $this->getMethod(), $this->getRequestUri());
    }

    protected function getParameters($parameterType, $name, array $filters = null, $defaultValue = null)
    {
        $parameter = $this->$parameterType;

        if (false !== ($start = mb_strpos($name, '[')) && false !== ($end = mb_strpos($name, ']'))) {

            $value = substr($name, 0, $start);

            if (!array_key_exists($value, $parameter)) {
                return $defaultValue;
            }

            if (preg_match_all('#\[(.*?)\]#', $name, $matches)) {

                $totalMatch = count($matches[1]);

                $key = $matches[1][0];

                if ($totalMatch > 1) {
                    // hazırlanacak array'ın arayı falan
                    die('hazirdegil abi daha');
                }

                return array_key_exists($key, $parameter[$value]) ? $parameter[$value][$key] : $defaultValue;
            }
        }

        return array_key_exists($name, $parameter) ? $parameter[$name] : $defaultValue;
    }

    protected function hasParameters($parameterType, $name)
    {
        return array_key_exists($name, $this->$parameterType);
    }

    public function has($name)
    {
        if ($this->hasParameters('query', $name) || $this->hasParameters('request', $name) || $this->hasParameters('files', $name) || $this->hasParameters('server', $name)) {
            return true;
        }

        return false;
    }

    public function hasPost($name)
    {
        return $this->hasParameters('request', $name);
    }

    public function hasPut($name)
    {
        return $this->hasParameters('request', $name);
    }

    public function hasQuery($name)
    {
        return $this->hasParameters('query', $name);
    }

    public function hasFile($name)
    {
        return $this->hasParameters('files', $name);
    }

    public function hasServer($name)
    {
        return $this->hasParameters('server', $name);
    }

    public function get($name, $defaultValue = null, $filters = null)
    {
        if ($value = $this->getParameters('query', $name, (array)$filters)) {
            return $value;
        } elseif ($value = $this->getParameters('request', $name, (array)$filters)) {
            return $value;
        } elseif ($value = $this->getParameters('files', $name, (array)$filters)) {
            return $value;
        } elseif ($value = $this->getParameters('cookies', $name, (array)$filters)) {
            return $value;
        }

        return $defaultValue;
    }

    public function getPost($name, $defaultValue = null, $filters = null)
    {
        return $this->getParameters('request', $name, $filters, $defaultValue);
    }

    public function getQuery($name, $defaultValue = null, $filters = null)
    {
        return $this->getParameters('query', $name, $filters, $defaultValue);
    }

    public function getPut($name, $defaultValue = null, $filters = null)
    {
        return $this->getParameters('request', $name, $filters, $defaultValue);
    }

    public function getServer($name, $defaultValue = null)
    {
        return $this->getParameters('server', $name, null, $defaultValue);
    }

    public function getFile($name, $defaultValue = null, $filters = null)
    {
        return $this->getParameters('files', $name, null, null);
    }

    public function getHeader($name, $defaultValue = null, $filters = null)
    {
        return $this->getParameters('headers', strtoupper($name), $filters, $defaultValue, null);
    }

    public function getCookie($name, $defaultValue = null, $filters = null)
    {
        return $this->getParameters('cookies', $name, $filters, $defaultValue, null);
    }

    public function getReferer()
    {
        return isset($this->headers['referer']) ? $this->headers['referer'] : NULL;
    }

    public function isAjax()
    {
        return 'XMLHttpRequest' == $this->getParameters('headers', 'X_REQUESTED_WITH');
    }

    public function getUserAgent()
    {
        return $this->getHeaders('USER_AGENT');
    }

    public function isJsonp()
    {
        return !empty($this->getQuery('callback'));
    }
}
