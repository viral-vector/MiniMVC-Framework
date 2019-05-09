<?php

namespace Mini\Ioc;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;

/**
 * Class Container
 * @package Mini\Ioc
 */
class Container
{
    /**
     * @var array
     */
    protected $alias = [];

    /**
     * @var array
     */
    protected $container = []; // ['key' => ['object' => '', 'shared' => false, 'sealed'  => false]];

    /**
     * @param $key
     * @return null|object
     */
    public function find($key)
    {
        if(!isset($this->container[$key])){
            return null;
        }

        return (object) $this->container[$key];
    }

    /**
     * @param $key
     * @param null $item
     * @return null|object
     */
    public function alias($key, $item = null)
    {
        if(!isset($item)){
            if(!isset($this->alias[$key])){
                return null;
            }

            return $this->find($this->alias[$key]);
        }

        $this->alias[$key] = $item;
    }

    /**
     * @param null $key
     * @param $object
     * @param bool $shared
     * @param bool $sealed
     * @return null|string
     */
    public function bind($key = null, $object, $shared = false, $sealed = false)
    {
        if(!isset($key)){
            if(is_object($object)){
                $key = get_class($object);
            }else if(class_exists($object)){
                $key = $object;
            }
        }

        if(!isset($key)){
            return null;
        }

        $binding = $this->find($key);

        if(isset($binding) && $binding->sealed){
            return $key;
        }

        $this->container[$key] = [ 'object' => $object, 'shared' => $shared, 'sealed' => $sealed ];

        return $key;
    }

    /**
     * @param $key
     * @param $object
     */
    protected function share($key, $object)
    {
        $this->container[$key]['object'] = $object;
    }

    /**
     * @param $key
     * @param array|null $parameters
     * @return mixed|null|object
     * @throws \ReflectionException
     */
    public function make($key, array $parameters = null)
    {
        $binding = $this->find($key)? : $this->alias($key);

        if(isset($binding)){
            $object = $binding->object;

            if($object instanceof Closure){
                return $this->call(null, $object, $parameters?:[]);
            }else if(is_array($object)){
                return $object;
            }else if(is_object($object) || !class_exists($object)){
                return $object;
            }else{
                return $this->manage($key, $this->build($object, $parameters?:[]));
            }
        }

        return $this->build($key, $parameters?:[] );
    }

    /**
     * @param $class
     * @param $function
     * @param array $parameters
     * @return mixed
     * @throws \ReflectionException
     */
    public function call($class, $function, array $parameters = [])
    {
        if(isset($class) && !is_object($class)){
            $class = $this->make($class);
        }

        if($function instanceof Closure){
            $reflection = new ReflectionFunction($function);
        }
        else{
            $reflection = new ReflectionMethod($class, $function);
        }

        $args = $this->resolve_params($reflection->getParameters(), $parameters);

        if($function instanceof Closure){
            return $reflection->invokeArgs($args? : []);
        }else{
            return $reflection->invokeArgs($class, $args? : []);
        }
    }

    /**
     * @param $class
     * @param null $parameters
     * @return null|object
     * @throws \ReflectionException
     */
    protected function build($class, $parameters = null)
    {
        $reflection = new ReflectionClass($class);

        if(!$reflection->isInstantiable()){
            return null;
        }

        $constructor = $reflection->getConstructor();

        if($constructor != null){
            $args = $this->resolve_params($constructor->getParameters(), $parameters);
        }

        return $reflection->newInstanceArgs($args ?? []);
    }

    /**
     * @param array $params
     * @param array $parameters
     * @return array
     */
    protected function resolve_params(array $params, array $parameters = [])
    {
        $args = [];

        foreach ($params as $param) {
            $value = null;
            $class = $param->getClass();

            if($class != null) {
                if($class->name == get_class($this)){
                    $value = $this;
                }
                else if(isset($parameters[$class->name])){
                    $value = $parameters[$class->name];
                }
                else{
                    $value = $this->make($class->name, null);
                }
            }else{
                $name = $param->name;

                if(isset($parameters[$name])){
                    $value = $parameters[$name];
                }
            }

            if(isset($value)){
                $args[$param->getPosition()] = $value;
            }
        }

        return $args;
    }

    /**
     * @param $key
     * @param $object
     * @return mixed
     */
    protected function manage($key, $object)
    {
        $binding = $this->find($key);

        if(isset($binding)){
            if($binding->shared){
                $this->share($key, $object);
            }
        }

        return $object;
    }

    /**
     * @param $item
     * @return mixed|null|object
     */
    public function __get($item)
    {
        return $this->make($item);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->container);
    }
}