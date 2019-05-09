<?php

namespace Mini\Routing;

use Mini\Exceptions\MResponseException;

/**
 * Class Response
 * @package Mini\Routing
 */
class Response
{
    /**
     * @var array $content
     */
    protected $content = [];

    /**
     * @var \Mini\Routing\Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $headers = [
        'Content-Type' => 'text/html',
        'Content-Disposition' => '',
        'Cache-Control' => 'no-cache',
        'Expires' => '',
    ];

    /**
     * @var array
     */
    protected $settings = [
        'status_code' => 200,
    ];

    /**
     * Response constructor.
     * @param \Mini\Routing\Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->content[] = '<p>MiniMVC (c) viral-vector' . date('y') .'</p>';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $output = '';

        for ($i=0; $i < count($this->content); $i++) {
            $output .= $this->content[$i];
        }

        return $output;
    }

    /**
     * @param $content
     */
    public function setContent($content)
    {
        $this->content = [$content];
    }

    /**
     * @param $content
     */
    public function putContent($content)
    {
        $this->content[] = $content;
    }

    /**
     * @return array
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setHeaders($key, $value)
    {
        if(!isset($key)){
            return;
        }

        $this->headers[$key] = $value;
    }

    /**
     * @param null $key
     * @return array|mixed
     */
    public function getHeaders($key = null)
    {
        if(isset($key)){
            return $this->headers[$key];
        }

        return $this->headers;
    }

    /**
     * @param \Mini\Routing\Request $request
     * @param null $content
     * @throws \Mini\Exceptions\MResponseException
     */
    public function respond(Request $request, $content = null)
    {
        if(isset($content))
            $this->setContent($content);

        if($this->valid($request)){
            $this->respondHeaders();
            $this->respondContent();
        }else{
            throw new MResponseException("Response Failure");
        }
    }

    /**
     * @param $url
     * @param int $code
     */
    public function redirect($url, $code = 302)
    {
        header('location: ' . $url, true, $code);
    }

    /**
     * @param $content
     */
    public function json($content)
    {
        $this->setHeaders('Content-Type', 'application/json');

        $this->setContent(json_encode($content));
    }

    /**
     * @param $filePath
     * @param null $name
     * @throws \Mini\Exceptions\MResponseException
     */
    public function download($filePath, $name = null)
    {
        $this->setHeaders('Accept-Ranges', 'bytes');
        $this->setHeaders('Content-Type', 'application/octet-stream');
        $this->setHeaders('Content-Disposition', 'attachment; filename=' . $name ?? basename($filePath));

        $this->setContent(NULL);

        try{
            set_time_limit(0);
            $file = @fopen($filePath, "rb");
            while(!feof($file)) {
                $this->putContent(@fread($file, 1024*8));
            }
        }catch(\Exception $e){
            throw new MResponseException("File Download Failure");
        }
    }

    /**
     * @param \Mini\Routing\Request $request
     * @return bool
     */
    protected function valid(Request $request)
    {
        $allowedContent = explode(',', $request->getHeaders('accept'));

        //if(in_array($this->headers['Content-Type'], $allowedContent)){
        return true;
        //}

        return false;
    }

    /**
     *
     */
    protected function respondHeaders()
    {
        if(headers_sent()) return;

        http_response_code($this->settings['status_code']);

        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
    }

    /**
     *
     */
    protected function respondContent()
    {
        echo $this->__toString();

        return;
    }
}
