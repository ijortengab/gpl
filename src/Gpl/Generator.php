<?php

namespace Gpl;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Process\Process;
use Gpl\FastCgi\PhpFpm;

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
        $this->filesystem = new Filesystem;
    }

    /**
     *
     */
    public function execute()
    {
        // $this->environment->getWebServer()->getGenerator()->execute();
        // $this->environment->getSite()->getGenerator()->execute();

        // Buat PHP PM.
        preg_match('/^(\d+)\.(\d+)\.(\d+)$/', PHP_VERSION, $mathces);
        $major = $mathces[1];
        $minor = $mathces[2];
        $version = $major.'.'.$minor;

        // @todo, path seperti ini ada pada Debian/Ubuntu dengan instalasi
        // melalui apt-get. Nanti perlu custom configuration (penyesuaian)
        // pada Distro yang lain atau instalasi melalui cara non de facto.
        $path = '/etc/php/'.$version.'/fpm/pool.d';
        if (!is_dir($path)) {
            // PHP FPM tidak ada.
            throw new Exception(strtr('PHP FPM versi `%version` tidak ditemukan.', ['%version' => $version]));
            // PHP FPM versi `7.4` tidak ditemukan.
        }
        $user_process = $this->environment->getUserProcess();
        $path = '/etc/php/'.$version.'/fpm/pool.d/'.$user_process.'.conf';
        $path_socket = "/run/php/php$version-fpm-$user_process.sock";
        // listen = /run/php/php7.4-fpm-ijortengab.sock
        if (!file_exists($path)) {
            $translate = [
                '{{user_process}}' => $user_process,
                '{{path_socket}}' => $path_socket,
                '{{webserver_listen_owner}}' => 'www-data',
                '{{webserver_listen_group}}' => 'www-data',
            ];
            $this->createPhpFpmFileConf($path, $translate, $version);
        }
        // Get host name.
        $site = $this->environment->getSite();
        $environment_name = $this->environment->getName();
        $host_name = $site->getHost($environment_name);
        $site_name = $site->getName();
        if (null === $host_name) {
            throw new \Exception(sprintf('Host name for "%s" site at "%s" environment unknown.', $site_name, $environment_name));
        }
        // Buat direktori web root.
        $web_server = $this->environment->getWebServer();
        $base_web_root = $web_server->getBaseWebRoot();
        $web_root = $base_web_root.'/'.$site_name.'.'.$environment_name;
        if (!is_dir($web_root)) {
            $this->createDirectoryWebRoot($web_root);
        }
        // @todo, path seperti ini ada pada Debian/Ubuntu dengan instalasi
        // melalui apt-get. Nanti perlu custom configuration (penyesuaian)
        // pada Distro yang lain atau instalasi melalui cara non de facto.
        $path = '/etc/nginx/sites-available/'.$host_name;
        if (!file_exists($path)) {
            $translate = [
                '{{web_root}}' => $web_root,
                '{{host_name}}' => $host_name,
                '{{path_socket}}' => $path_socket,
            ];
            $this->createNginxVirtualHostFile($path, $translate);
        }
        $path_symlink = '/etc/nginx/sites-enabled/'.$host_name;
        if (!file_exists($path_symlink)) {
            $this->createNginxVirtualHostSymlink($path, $path_symlink);
        }
        // Untuk site generator, serahkan pada object site.
        $site_generator = $site->getGenerator();
        $site_generator->setWorkingDirectory($web_root);
        $site_generator->execute();
    }

    /**
     *
     */
    protected function createPhpFpmFileConf($path, $translate, $version)
    {
        $user_process = $this->environment->getUserProcess();
        $content = PhpFpm::CONTENT_CONFIG_DEFAULT;
        // Make sure of EOL.
        $content = rtrim($content).PHP_EOL;
        // Translate.
        $content = strtr($content, $translate);
        $need_restart = false;
        try {
            // Coba pake user biasa.
            try {
                $this->filesystem->dumpFile($path, $content);
            }
            catch (IOException $e) {
                echo "Gagal.\n";
                echo $e->getMessage()."\n";
                echo "Coba lagi.\n";
                $chmod_before = substr(sprintf('%o', fileperms(dirname($path))), -4);
                $process = new Process(['sudo', 'chmod', '0777', dirname($path)]);
                $process->run();
                $this->filesystem->dumpFile($path, $content);
                $need_restart = true;
            }
            finally {
                // Balikin lagi ke chmod semula.
                $process = new Process(['sudo', 'chmod', $chmod_before, dirname($path)]);
                $process->run();
            }
        }
        catch (IOException $e) {
            echo "Gagal lagi.\n";
            echo $e->getMessage()."\n";
            throw new \Exception(sprintf('Gagal membuat file "%s".', $path));
        }
        finally {
            if ($need_restart) {
                echo "Restart php-fpm.\n";
                try {
                    $path = '/etc/init.d/php'.$version.'-fpm';
                    if (!file_exists($path)) {
                        throw new \Exception(sprintf('Gagal restart PHP FPM karena file script tidak ada "%s".', $path));
                        // Gagal restart PHP FPM karena file script tidak ada "/etc/init.d/php7.4-fpm".
                    }
                    // @todo, bagaimana jika gagal restart.
                    // perlu di notice.
                    $process = new Process(['sudo', $path, 'restart']);
                    $process->run();
                }
                catch (\Exception $e) {
                    echo $e->getMessage().PHP_EOL;
                }
            }
        }
    }
    /**
     *
     */
    protected function createDirectoryWebRoot($path)
    {
        try {
            // Coba pake user biasa.
            try {
                $this->filesystem->mkdir($path);
            }
            catch (IOException $e) {
                echo "Gagal.\n";
                echo $e->getMessage()."\n";
                echo "Coba lagi.\n";
                $chmod_before = substr(sprintf('%o', fileperms(dirname($path))), -4);
                $process = new Process(['sudo', 'chmod', '0777', dirname($path)]);
                $process->run();
                $this->filesystem->mkdir($path);
            }
            finally {
                // Balikin lagi ke chmod semula.
                $process = new Process(['sudo', 'chmod', $chmod_before, dirname($path)]);
                $process->run();
            }
        }
        catch (Exception $e) {
            echo "Gagal lagi.\n";
            echo $e->getMessage()."\n";
            throw new \Exception(sprintf('Gagal membuat direktori "%s".', $path));
        }
    }
    /**
     *
     */
    protected function createNginxVirtualHostFile($path, $translate)
    {
        $handler = $this->environment->getWebServer()->getHandler();
        $content = $handler::CONTENT_CONFIG_DEFAULT;
        // Make sure of EOL.
        $content = rtrim($content).PHP_EOL;
        // Translate.
        $content = strtr($content, $translate);
        // $need_restart = false;
        // @todo: jika nginx belum jalan, maka jangan reload.

        try {
            // Coba pake user biasa.
            try {
                $this->filesystem->dumpFile($path, $content);
            }
            catch (IOException $e) {
                echo "Gagal.\n";
                echo $e->getMessage()."\n";
                echo "Coba lagi.\n";
                $chmod_before = substr(sprintf('%o', fileperms(dirname($path))), -4);
                $process = new Process(['sudo', 'chmod', '0777', dirname($path)]);
                $process->run();
                $this->filesystem->dumpFile($path, $content);

            }
            finally {
                // Balikin lagi ke chmod semula.
                $process = new Process(['sudo', 'chmod', $chmod_before, dirname($path)]);
                $process->run();
            }
        }
        catch (IOException $e) {
            echo "Gagal lagi.\n";
            echo $e->getMessage()."\n";
            throw new \Exception(sprintf('Gagal membuat file "%s".', $path));
        }
    }

    /**
     *
     */
    protected function createNginxVirtualHostSymlink($path, $path_symlink)
    {
        $need_reload = false;
        try {
            // Coba pake user biasa.
            try {
                $this->filesystem->symlink($path, $path_symlink);
            }
            catch (IOException | FileNotFoundException $e) {
                echo "Gagal.\n";
                echo $e->getMessage()."\n";
                echo "Coba lagi.\n";
                $chmod_before = substr(sprintf('%o', fileperms(dirname($path_symlink))), -4);
                $process = new Process(['sudo', 'chmod', '0777', dirname($path_symlink)]);
                $process->run();
                $this->filesystem->symlink($path, $path_symlink);
                $need_reload = true;
            }
            finally {
                // Balikin lagi ke chmod semula.
                $process = new Process(['sudo', 'chmod', $chmod_before, dirname($path_symlink)]);
                $process->run();
            }
        }
        catch (IOException | FileNotFoundException $e) {
            echo "Gagal lagi.\n";
            echo $e->getMessage()."\n";
            throw new \Exception(sprintf('Gagal membuat link "%s".', $path));
        }
        finally {
            if ($need_reload) {
                echo "Reload nginx.\n";
                // @todo, bagaimana jika gagal reload. perlu di notice.
                $process = new Process(['sudo', 'nginx', '-s', 'reload']);
                $process->run();
            }
        }
    }
}
