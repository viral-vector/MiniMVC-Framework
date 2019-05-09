<?php 

use Mini\Mini;

if(! function_exists('mini'))
{	
	/**
     * Get the available container instance.
     *
     * @param  string  $make
     * @param  array   $parameters
     * @return mixed|\Mini\Mini
     */
    function mini($make = null, array $parameters = [])
    {
        if (is_null($make)) {
            return Mini::mini();
        }

        return Mini::mini()->make($make, $parameters);
    }
}

if(! function_exists('mini_debug') )
{
    /**
     *
     */
	function mini_debug()
	{		
		dump(func_get_args(), Mini::benchmark(), debug_backtrace());
	}
}

if(! function_exists('mini_exception') )
{
    /**
     * @param \Exception $exception
     */
	function mini_exception(\Exception $exception)
	{
		 die(mini_debug($exception));
	}
}