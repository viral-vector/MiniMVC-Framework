<?php 

namespace Mini\Components\Filters;

use Mini\Routing\Request;
use Mini\Routing\Response;

/**
 * Interface FilterInterface
 * @package Mini\Components\Filters
 */
interface FilterInterface
{
    /**
     * @param \Mini\Routing\Request $request
     * @param \Mini\Routing\Response $response
     * @return mixed
     */
    function handle(Request $request, Response $response);
}