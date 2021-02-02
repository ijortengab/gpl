<?php

namespace Gpl;

class Site
{
    protected $name;
    protected $generator;
    protected $generator_object;
    protected $host = [];

    /**
     *
     */
    public function __construct($name, array $info = [])
    {
        $this->name = $name;
        if (isset($info['host'])) {
            $this->host = $info['host'];
        }
        if (isset($info['_generator'])) {
            $this->generator = $info['_generator'];
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
        switch ($this->generator) {
            case 'drupal':
                if (null === $this->generator_object) {
                    $this->generator_object = new Generator\Drupal;
                }
                return $this->generator_object;
                break;

            case '':
                // Do something.
                break;

            default:
                // Do something.
                break;
        }

    }
}
