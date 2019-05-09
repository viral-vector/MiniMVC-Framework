<?php

namespace Mini\Exceptions;

use \Exception;

/**
 * Class MException
 * @package Mini\Exceptions
 */
abstract class MException extends Exception
{
    /**
     * MException constructor.
     * @param null $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = null, $code = 404, Exception $previous = null)
    {
        parent::__construct (get_class($this) . ': ' . $message, $code, $previous);
    }
}