<?php

namespace Mini;

use Mini\Ioc\Container;
use Mini\Exceptions\MException;

use Mini\Routing\Router;
use Mini\Routing\Request;
use Mini\Routing\Response;
use Mini\Components\Actions\ActionHandler;
use Mini\Components\Session\SessionManager;

/**
 * Class Mini
 * @package Mini
 */
class Mini extends Container
{
    /**
     * @var
     */
    protected static $mini;

    /**
     * Application Path
     *
     * @var $appPath
     */
    public $appPath;

    /**
     * Mini constructor.
     * @param $path
     */
    function __construct($path)
    {
        // 1. App Start time
        define('MiniMVC_START', microtime(true));

        // 2. App base path
        $this->setPath($path);

        // 3. Boot Framework
        $this->boot();

        // 4. Init Self-Refrence
        static::$mini = $this;
    }

    /**
     * @param string $locale
     * @return string
     */
    public function getPath($locale = '')
    {
        $path = $this->appPath;

        if($locale){
            $path .= DIRECTORY_SEPARATOR . $locale;
        }

        return $path;
    }

    /**
     * @param $path
     */
    protected function setPath($path)
    {
        $this->appPath = $path;
    }

    /**
     * @param array $register
     * @return $this
     */
    public function register(array $register)
    {
        $this->registerFilters($register['filters']);

        $this->registerActions($register['actions']);

        return $this;
    }

    /**
     * @param array $register
     */
    protected function registerFilters(array $register)
    {
        foreach ($register as $key => $filter) {
            $this->bind('filter.' . $key, $filter);
        }
    }

    /**
     * @param array $register
     */
    protected function registerActions(array $register)
    {
        foreach ($register as $alias => $action) {
            $this->handler->action($alias, $action['class']);

            if(isset($action['listeners'])) {
                foreach ($action['listeners'] as $listener) {
                    $this->handler->listen($alias, $listener);
                }
            }
        }
    }

    /**
     * @param array $routes
     * @return $this
     */
    public function routes(array $routes)
    {
        $router = $this->make(Router::class);

        foreach ($routes as $key => $route){
            if(is_numeric($key)){
                $this->routes($route); continue;
            }

            try{
                $router->build($key, $route);
            }catch (\Exception $e){
                mini_exception($e);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function boot()
    {
        $this->alias('session', $this->bind(null, SessionManager::class, true, true));

        // Router
        $this->alias('router', $this->bind(null, Router::class, true, true));

        // Request
        $this->alias('request', $this->bind(null, $this->make(Request::class), true, true));

        // Actions
        $this->alias('handler', $this->bind(null, $this->make(ActionHandler::class), true, true));

        return $this;
    }

    /**
     *
     */
    public function run()
    {
        // Session
        $session = $this->make(SessionManager::class);

        // Response
        $this->alias('response', $this->bind(null, $this->make(Response::class), true, true));

        try{
            $this->response->respond($this->request, $this->router->resolve($this->request));
        } catch(MException $e) {
            mini_exception($e);
        }

        static::benchmark();
    }

    /**
     * Execute Action
     *
     * @var $action
     * @var array $data
     */
    public function fire($action = '', array $data = [])
    {
        $this->handler->handle($action, $data);
    }

    /**
     * instance
     *
     * @return static
     */
    public static function mini()
    {
        if (is_null(static::$mini)) {
            static::$mini = new static;
        }

        return static::$mini;
    }

    /**
     * @return array
     */
    public static function benchmark()
    {
        // Benchmarks
        //time
        $duration = microtime(true) - MiniMVC_START;

        //memory
        $size = memory_get_usage(false);
        $unit = array('b','kb','mb','gb','tb','pb');
        $uram = @round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' .$unit[$i];

        return ['duration' => $duration, 'memory' => $uram];
    }
}