<?php

class tea_apc extends session_apt
{
    public function __construct($options = [])
    {
        if (!$this->test()) {
            throw new Exception("The apc extension isn't available");
        }
        $this->register();
    }

    public function open($save_path, $session_name)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        $sess_id = 'sess_'.$id;

        return (string) apc_fetch($sess_id);
    }

    public function write($id, $session_data)
    {
        $sess_id = 'sess_'.$id;

        return apc_store($sess_id, $session_data, ini_get('session.gc_maxlifetime'));
    }

    public function destroy($id)
    {
        $sess_id = 'sess_'.$id;

        return apc_delete($sess_id);
    }

    public function gc($maxlifetime)
    {
        return true;
    }

    public function test()
    {
        return extension_loaded('apc');
    }
}
