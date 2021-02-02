<?php

namespace Gpl;

class WebServer
{
    protected $name;
    protected $base_root;
    protected $program;

    /**
     *
     */
    public function __construct($name, array $info = [])
    {
        $this->name = $name;
        if (isset($info['_base_root'])) {
            $this->base_root = $info['_base_root'];
        }
        if (isset($info['_program'])) {
            $this->program = $info['_program'];
        }
        return $this;
    }


    /**
     *
     */
    public function getBaseWebRoot()
    {

        return $this->base_root;
    }

}
