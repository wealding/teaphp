<?php

class controller
{
    public $tea;
    public $view = null;
    public $session;
    private $__template_vals = [];

    public function __construct($tea)
    {
        $this->tea = $tea;
    }

    public function __set($name, $value)
    {
        $this->__template_vals[$name] = $value;
    }

    public function __get($name)
    {
        return $this->__template_vals[$name];
    }

    public function display($tplname, $output = true)
    {
        if ($this->tea->view === null) {
            $this->tea->init(['view']);
        }
        if ($this->view === null) {
            $this->view = $this->tea->view;
        }
        if (!empty($this->__template_vals)) {
            $this->view->_view->assign($this->__template_vals);
        }
        @ob_start();
        $this->view->display($tplname);
        if (true != $output) {
            return ob_get_clean();
        }
    }

    public function __call($name, $args)
    {
    }
}
