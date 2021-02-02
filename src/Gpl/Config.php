<?php

namespace Gpl;

class Config
{
    protected $array;

    /**
     *
     */
    public function __construct($array)
    {
        $this->array = $array;
        return $this;
    }

    /**
     *
     */
    public function getEnvironmentInfo($string)
    {
        if (isset($this->array['environment'][$string])) {
            return $this->array['environment'][$string];
        }
    }

    /**
     *
     */
    public function getWebServerInfo($string)
    {
        if (isset($this->array['web_server'][$string])) {
            return $this->array['web_server'][$string];
        }
    }

    /**
     *
     */
    public function getSiteInfo($string)
    {
        if (isset($this->array['site'][$string])) {
            return $this->array['site'][$string];
        }
    }
}
