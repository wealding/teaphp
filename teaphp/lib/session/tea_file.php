<?php

class tea_file extends session_apt
{
    public function __construct($options = [])
    {
        ini_set('session.save_handler', 'files');
        session_save_path($path);
    }
}
