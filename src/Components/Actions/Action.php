<?php

namespace Mini\Components\Actions;

/**
 * Class Action
 * @package Mini\Components\Actions
 */
class Action
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $data;

    /**
     * Action constructor.
     * @param $name
     * @param null $data
     */
    function __construct($name, $data = null)
    {
        if(!isset($name)){
            $name = get_class($this);
        }

        $this->name = $name;

        $this->data = serialize($data);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return unserialize($this->data);
    }
}