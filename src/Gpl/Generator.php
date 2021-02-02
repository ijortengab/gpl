<?php

namespace Gpl;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;

class Generator
{
    protected $environment;
    protected $filesystem;

    /**
     *
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     *
     */
    public function buildSite()
    {
        // Buat direktori untuk webroot.
        $web_server = $this->environment->getWebServer(); // Object.
        $site = $this->environment->getSite(); // Object.
        $environment_name = $this->environment->getName();
        $host_name = $site->getHost($environment_name);
        $site_name = $site->getName();
        if (null === $host_name) {
            throw new \Exception(strtr("Host name for '%site' site at '%environment' environment unknown.", [
                '%site' => $site_name,
                '%environment' => $environment_name,
            ]));
        }
        // Rakit direktori webroot.
        $webroot = $web_server->getBaseWebRoot();
        $webroot .= DIRECTORY_SEPARATOR.$site_name.'.'.$environment_name;
        $this->filesystem = new Filesystem;
        // Check direktori webroot.
        try {
            $this->filesystem->mkdir($webroot);
        }
        catch (IOException $e) {
            // @todo gunakan ssh.
            // chown root:root /var/www
            // chown ijortengab:ijortengab /var/www
            throw new \Exception($e->getMessage());
        }
        $site_generator = $site->getGenerator();
        $site_generator->setWorkingDirectory($webroot);
        $site_generator->execute();




    }

}
