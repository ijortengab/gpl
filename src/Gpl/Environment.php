<?php

namespace Gpl;

class Environment
{

    protected $user_process;
    protected $name;
    protected $label;
    protected $config;
    protected $web_server;
    protected $site;

    /**
     *
     */
    public function __construct($name)
    {
        // Validate.
        if (empty($name)) {
            throw new Exception('Environment name not defined.');
        }
        $this->name = $name;
        $uid = \posix_getpwuid(posix_geteuid());
        $this->user_process = $uid['name'];
        return $this;
    }

    /**
     *
     */
    public function getUserProcess()
    {

        return $this->user_process;
    }


    /**
     *
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     *
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Build all object instance that relate with environment.
     */
    public function populateAll()
    {
        $environment_info = $this->config->getEnvironmentInfo($this->name);
        if (isset($environment_info['_label'])) {
            $this->label = $environment_info['_label'];
        }
        if (isset($environment_info['web_server'])) {
            $web_server_info = $this->config->getWebServerInfo($environment_info['web_server']);
            if (empty($web_server_info)) {
                throw new Exception(strtr('Web server info not defined: %webserver.', ['%webserver' => $environment_info['web_server']]));
            }
            $this->web_server = new WebServer($environment_info['web_server'], $web_server_info);
        }
        if (isset($environment_info['site'])) {
            $site_info = $this->config->getSiteInfo($environment_info['site']);
            if (empty($site_info)) {
                throw new Exception(strtr('Site info not defined: %site.', ['%site' => $environment_info['site']]));
            }
            $this->site = new Site($environment_info['site'], $site_info);
        }
    }

    /**
     *
     */
    public function getWebServer()
    {
        return $this->web_server;
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
    public function getSite()
    {
        return $this->site;
    }
}
