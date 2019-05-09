<?php 

namespace Mini\Components\Session;

/**
 * Class SessionManager
 * @package Mini\Components\Session
 */
class SessionManager{

    /**
     * @var
     */
    protected $handler;

    /**
     * @param \SessionHandlerInterface $handler
     */
	public function boot(\SessionHandlerInterface $handler)
	{
        $this->handler = $handler;

        session_set_save_handler($handler);

        session_set_cookie_params (
            3600,
            '/',
            '.example.com',
            true,
            false
        );

        session_start([
            'cookie_lifetime' => 86400,
        ]);
	}

    /**
     * @return \SessionHandlerInterface
     */
    public function handler()
    {
       return $this->handler;
    }

    /**
     * @return array
     */
    public function flush()
    {
        $values = self::all();

        foreach ($_SESSION as $key => $value){
            $this->pull($key);
        }

        return $values;
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function set($key, $value)
    {
        return $_SESSION[$key] = $value;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function pull($key)
    {
        $value = self::get($key);

        unset($_SESSION[$key]);

        return $value;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $_SESSION[$key];
    }

    /**
     * @return mixed
     */
    public static function all()
    {
        return $_SESSION;
    }
}