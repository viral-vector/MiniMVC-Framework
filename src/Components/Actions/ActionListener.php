<?php

namespace Mini\Components\Actions;

/**
 * Interface ActionListener
 * @package Mini\Components\Actions
 */
interface ActionListener
{
    /**
     * @param \Mini\Components\Actions\Action $action
     * @return mixed
     */
    public function handle(Action $action);
}
 