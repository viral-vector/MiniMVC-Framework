<?php 

namespace Mini\Components\Session;

/**
 * Class SessionFile
 * @package Mini\Components\Session
 */
class SessionFile implements \SessionHandlerInterface{

    /**
     * SessionFile constructor.
     */
    public function __construct()
    {
        session_save_path(mini_path("/storage/session"));
    }

    /**
     * @return bool|void
     */
    public function close()
    {
        // TODO: Implement close() method.
    }

    /**
     * @param string $session_id
     * @return bool|void
     */
    public function destroy($session_id)
    {
        // TODO: Implement destroy() method.
    }

    /**
     * @param int $maxlifetime
     * @return bool|void
     */
    public function gc($maxlifetime)
    {
        // TODO: Implement gc() method.
    }

    /**
     * @param string $save_path
     * @param string $name
     * @return bool|void
     */
    public function open($save_path, $name)
    {
        // TODO: Implement open() method.
    }

    /**
     * @param string $session_id
     * @return string|void
     */
    public function read($session_id)
    {
        // TODO: Implement read() method.
    }

    /**
     * @param string $session_id
     * @param string $session_data
     * @return bool|void
     */
    public function write($session_id, $session_data)
    {
        // TODO: Implement write() method.
    }
}