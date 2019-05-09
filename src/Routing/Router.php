<?php

namespace Mini\Routing;

use Mini\Routing\Route;
use Mini\Routing\Response;

use Mini\Exceptions\MRouteException;

/**
 * Class Router
 * @package Mini\Routing
 */
class Router
{
    /**
     * @var array|object
     */
    protected $routes = [];

    /**
     * Router constructor.
     */
    function __construct()
    {
        $this->routes = (object) [
            'get'    => [],
            'post'   => [],
            'put'    => [],
            'delete' => []
        ];
    }

    /**
     * @return string
     */
    function __toString()
    {
        return json_encode($this->routes);
    }

    /**
     * @param $route
     * @param array $configs
     */
    function build($route, array $configs)
    {
        foreach ($configs['methods'] as $method){
            if(empty($method)) continue;

            $object = (object)[
                'execute' => $configs['execute'],
                'filters' => $configs['filters'] ?? [],
            ];

            if(strpos($route, '{') !== false){
                $regexed = $route;

                preg_match_all('~\{([^}]+)\}~', $regexed, $attribs);

                for ($i = 0; $i < count($attribs[0]); $i++) {
                    $matcher = isset($configs['matches'][$attribs[1][$i]])?
                        '('. $configs['matches'][$attribs[1][$i]] .')' : null;

                    $regexed = str_replace(
                        $attribs[0][$i],
                        $matcher ?? "([^/]+)",
                        $regexed
                    );
                }

                $hashed = hash('crc32', $regexed);

                $object->attribs = $attribs[1] ?? NULL;
                $object->regexed = $regexed ."(*MARK:$hashed)";

                $this->routes->$method[$hashed] = $object;
            }else{
                $this->routes->$method[$route] = $object;
            }
        }
    }

    /**
     * @param $list
     * @param $path
     * @return mixed
     * @throws \Mini\Exceptions\MRouteException
     */
    protected function match($list, $path)
    {
        if(isset($list[$path])){
            $route = $list[$path];
        }else{
            $regex = [];
            foreach ($list as $key => $configs) {
                if(!isset($configs->regexed)) continue;

                $regex[] = $configs->regexed;
            }

            $regex = '~^(?|' . implode($regex, '|') . ')$~x';

            if(preg_match($regex, $path, $matches) === 0){
                throw new MRouteException("$path not found!");
            }

            $route = $list[$matches['MARK']];

            $params = [];
            for($i = 1; $i < count($matches) - 1; $i++){
                $params[$route->attribs[$i-1]] = $matches[$i];
            }
        }

        // mini_debug($regex ?? [], $path, $matches ?? [], $list, $route, $params);

        return [$route, $params ?? []];
    }

    /**
     * @param \Mini\Routing\Request $request
     * @return mixed
     * @throws \Mini\Exceptions\MRouteException
     * @throws \ReflectionException
     */
    function resolve(Request $request)
    {
        $type = $request->type();
        $path = $request->path();

        $list = $this->routes->$type;

        list($route, $params) = $this->match($list, $path);

        foreach ($params as $key => $value) {
            $request->$key = $value;
        }

        // mini_debug($request, $route, $params);

        if($this->filter($request, $route)){
            return $this->execute($request, $route);
        }
    }

    /**
     * @param \Mini\Routing\Request $request
     * @param $route
     * @return bool|mixed
     * @throws \Mini\Exceptions\MRouteException
     * @throws \ReflectionException
     */
    protected function filter(Request $request, $route)
    {
        $result = true;

        if(isset($route->filter)){
            foreach ($route->filter as $filter) {
                $result = mini()->call('filter.' . $filter, 'handle');

                if($result != true){
                    throw new MRouteException("$filter Failure");
                }
            }
        }

        return $result;
    }

    /**
     * @param \Mini\Routing\Request $request
     * @param $route
     * @return mixed
     * @throws \ReflectionException
     */
    protected function execute(Request $request, $route)
    {
        if(is_object($route->execute)){
            $object = null;
            $method = $route->execute;
        }else{
            $call = explode('.', $route->execute);

            $object = $call[0];
            $method = $call[1];
        }

        return mini()->call($object, $method, $request->all());
    }
}