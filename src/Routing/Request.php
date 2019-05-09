<?php

namespace Mini\Routing;

use Mini\Exceptions\MRequestException;

/**
 * Class Request
 * @package Mini\Routing
 */
class Request
{
    /**
     * @var array $methods
     */
    protected $methods = [ 'get', 'post', 'put', 'delete'];

    /**
     * @var array $methods
     */
    protected $content = [ 'content_type', 'content_lenght', 'content_md5'];


    /**
     * HTT headers
     *
     *  @var array $this->headers
     */
    protected $headers = [

    ];

    /**
     * HTTP Method type
     *
     * @var array $request
     */
    protected $request = [
        'type' => '',
        'path' => [],
        'data' => [],
        'file' => [],
        'ajax' => false,
    ];

    /**
     * Request constructor.
     * @throws \Mini\Exceptions\MRequestException
     */
    function __construct()
    {
        $this->loadHeaders();
        $this->loadRequest();
        $this->realRequest();
    }

    /**
     *
     */
    protected function loadHeaders()
    {
        foreach ($_SERVER as $key => $value) {
            $key = strtolower($key);

            if (substr($key, 0, 5) == 'http_') {
                $key = substr($key,5);

                $this->headers[$key] = $value;
            } else {
                if(in_array($key, $this->content)){
                    $this->headers[$key] = $value;
                }
            }
        }

        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $this->headers['authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }
        elseif (isset($_SERVER['PHP_AUTH_USER'])) {
            $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';

            $this->headers['authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
        }
        elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
            $this->headers['authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
        }
    }

    /**
     *
     */
    protected function loadRequest()
    {
        $type = strtolower($_SERVER['REQUEST_METHOD']);
        $qstr = strtolower($_SERVER['QUERY_STRING']);
        $path = strtolower($_SERVER['REQUEST_URI']);

        if($path !== '/'){
            $path = explode('?', $path)[0];
        }

        if(!empty($qstr)){
            $qstr = explode('&', $qstr);
            foreach ($qstr as $key => $value) {
                $str = explode('=', strtolower($value));

                $this->request['data'][$str[0]] = $str[count($str) - 1];
            }
        }

        $this->request['type'] = $type;
        $this->request['path'] = $path;

        array_merge($this->request['data'], !empty($_POST)? $_POST : $_GET);

        $this->request['ajax'] = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

        $this->request['file'] = $_FILES; // --- @TODO:: further file handling
    }

    /**
     * @throws \Mini\Exceptions\MRequestException
     */
    protected function realRequest()
    {
        if(!in_array($this->request['type'], $this->methods)){
            throw new MRequestException("Request method not accepted");
        }
    }

    /**
     * @return mixed
     */
    public function type()
    {
        return $this->request['type'];
    }

    /**
     * @return mixed
     */
    public function path()
    {
        return $this->request['path'];
    }

    /**
     * @return mixed
     */
    public function ajax()
    {
        return $this->request['ajax'];
    }

    /**
     * @param $key
     * @return mixed
     */
    public function file($key)
    {
        return $this->request['file'][$key];
    }

    /**
     * @param $key
     * @return mixed
     */
    public function data($key)
    {
        return $this->request['data'][$key];
    }

    /**
     * @return mixed
     */
    public function all()
    {
        return $this->request['data'];
    }

    /**
     * @param null $key
     * @return array|mixed
     */
    public function getHeaders($key = null)
    {
        if(isset($key))
        {
            return $this->headers[$key];
        }

        return $this->headers;
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->request['data'][$key] = $value;
    }
}
