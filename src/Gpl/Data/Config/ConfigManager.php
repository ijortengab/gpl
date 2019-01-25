<?php
namespace Gpl\Data\Config;

use Gpl\Drupal\Variable\VariableManager as Variable;

class ConfigManager
{
    /**
     * Penyimpanan values hasil method detectDirectory().
     */
    protected $directory;

    /**
     * Penyimpanan values hasil method scan().
     */
    protected $files;

    /**
     * Mengisi property $directory dengan memastikan bahwa direktori default
     * exists. Direktori default dari config adalah `private://gpl/config`.
     */
    public function detectDirectory()
    {
        $file_private_path = Variable::get('file_private_path');
        if (is_dir($file_private_path.'/gpl/config')) {
            $this->directory = $file_private_path.'/gpl/config';
        }
        return $this;
    }

    /**
     * Melakukan pencarian file YAML.
     */
    public function scan()
    {
        if (null === $this->directory) {
            return;
        }
        $this->files = file_scan_directory($this->directory, '/^.*\.yml$/');
        return $this;
    }

    /**
     * Memberikan property $files.
     */
    public function getFiles()
    {
        return $this->files;
    }
}
