<?php

namespace Mini\Components\Actions;

use Mini\Components\Actions\Action;
use Mini\Components\Actions\ActionListener;
use Mini\Exceptions\MActionException;

/**
 * Class ActionHandler
 * @package Mini\Components\Actions
 */
class ActionHandler
{
    /**
     *
     * @var array Mini\Actions\Action
     */
    protected $actions = [];

    /**
     *
     * @var array Mini\Actions\ActionListener
     */
    protected $listeners = [];

    /**
     * @param null $alias
     * @param null $data
     * @throws \Mini\Exceptions\MActionException
     * @throws \ReflectionException
     */
    public function handle($alias = null, $data = null)
    {
        if(!isset($this->actions[$alias])){
            throw new MActionException("$alias action has not been defined!");
        }

        $action = mini($this->actions[$alias], ['name' => $alias, 'data' => $data]);

        foreach ($this->listeners[$alias] as $class){
            $listener = mini($class);

            if(($listener instanceof ActionListener) == false){
                throw new MActionException("$listener is not an instance of " . get_class(ActionListener::class));
            }

            mini()->call($listener, 'handle', [Action::class => $action]);
        }
    }

    /**
     * @param null $alias
     * @param null $action
     */
    public function action($alias = null, $action = null)
    {
        if(!isset($this->listeners[$alias])){
            $this->listeners[$alias] = [];
        }

        $this->actions[$alias] = $action;
    }

    /**
     * @param null $alias
     * @param $listener
     * @param bool $listen
     */
    public function listen($alias = null, $listener, $listen = true)
    {
        if(!isset($this->listeners[$alias])){
            $this->listeners[$alias] = [];
        }

        $this->listeners[$alias][] = $listener;
    }
}