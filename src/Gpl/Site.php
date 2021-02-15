<?php

namespace Gpl;

class Site
{
    protected $name;
    protected $generator;
    protected $host = [];

    /**
     *
     */
    public function __construct($name, array $info = [])
    {
        $this->name = $name;
        // pe er disini
        if (isset($info['host'])) {
            $this->host = $info['host'];
        }
        if (isset($info['generator'])) {
            switch ($info['generator']) {
                case 'drupal':
                    $this->generator = new Site\Drupal;
                    break;
            }
        }
        return $this;
    }

    /**
     *
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     */
    public function getHost($string)
    {
        if (array_key_exists($string, $this->host)) {
            return $this->host[$string];
        }
    }

    /**
     *
     */
    public function getGenerator()
    {
        return $this->generator;
    }
    /**
     *
     */
    public function build()
    {

        // return $this;
    }

}
