<?php

if(! function_exists('mini_path'))
{
    /**
     * Return a qualified path
     *
     * @param  string  $dirPath
     * @param  string  $file
     * @return mixed|\Mini\Mini
     */
    function mini_path($dirPath = 'app', $file = null)
    {
        $path =  mini()->getPath($dirPath);

        if($file && file_exists($path . DIRECTORY_SEPARATOR . $file)){
            return $path . DIRECTORY_SEPARATOR . $file;
        }

        return $path . DIRECTORY_SEPARATOR;
    }
}

if(! function_exists('mini_boot'))
{
    /**
     * include some boot file
     *
     * @param string $dirPath
     * @param $file
     * @return mixed
     */
    function mini_boot($dirPath = 'app', $file)
    {
        $path = mini_path($dirPath, $file);
        
        if($file && is_file($path)){
            return include($path);
        }
    }
}