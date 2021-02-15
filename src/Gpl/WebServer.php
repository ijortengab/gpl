<?php

namespace Gpl;

class WebServer
{
    protected $name;
    protected $base_root;
    protected $handler;

    /**
     *
     */
    public function __construct($name, array $info = [])
    {
        $this->name = $name;
        $this->populateDefault();
        // Override.
        switch ($name) {
            case 'nginx':
                $this->handler = new WebServer\Nginx;
                break;
        }

        if (isset($info['_base_root'])) {
            $this->base_root = $info['_base_root'];
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

    /**
     *
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     *
     */
    protected function check()
    {
        echo "Memeriksa konfigurasi Nginx.\n";
        // @todo.
        return false;
    }

    /**
     *
     */
    protected function populateDefault()
    {

        // return $this;
    }

}
