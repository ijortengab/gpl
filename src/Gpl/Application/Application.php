<?php
namespace Gpl\Application;

use Gpl\Data\Config\ConfigManager;
use Gpl\Data\Content\ContentManager;
use Gpl\Data\Structure\StructureManager;
use Gpl\Drupal\Bootstrap\Bootstrap;
use Gpl\Drupal\Variable\VariableManager as Variable;
use Symfony\Component\Yaml\Yaml;

class Application
{
    /**
     * Array untuk menampung object yang akan melakukan update informasi
     * kedalam database.
     */
    protected static $write_register = [];

    /**
     * Object instance dari ConfigManager.
     */
    protected $config;

    /**
     * Object instance dari ContentManager.
     */
    protected $content;

    /**
     * Object instance dari StructureManager.
     */
    protected $structure;

    /**
     * Method yang digunakan untuk mendaftarkan object yang akan ditampung
     * kedalam property $write_register.
     */
    public static function writeRegister($object)
    {
        self::$write_register[] = $object;
        // return $this;
    }

    /**
     * Memulai instance.
     */
    public function __construct()
    {
        Bootstrap::load();
        $this->config = new ConfigManager();
        $this->content = new ContentManager();
        $this->structure = new StructureManager();
    }

    /**
     * Menjalankan keseluruhan proses aplikasi.
     */
    public function run()
    {
        return $this->commandDefault();
    }

    /**
     * Melakukan proses menulis didatabase. Semua object yang telah ter-register
     * di property static $write_register akan menjalankan method write().
     */
    public function write()
    {
        $list = self::$write_register;
        foreach ($list as $object) {
            $object->write();
        }
        Variable::write();
    }

    /**
     * Command default. Secara default command yang digunakan adalah generate.
     */
    protected function commandDefault()
    {
        return $this->commandGenerate();
    }

    /**
     * Command untuk membaca config dan menggenerate Drupal.
     */
    protected function commandGenerate()
    {
        $this->config->detectDirectory()->scan();
        $files_config = $this->config->getFiles();
        foreach ($files_config as $path => $info) {
            $name = $info->name;
            $object = $this->getObjetFromAddress($name);
            $contents = file_get_contents($path);
            $yaml = Yaml::parse($contents);
            $object->setYaml($yaml)->analyze()->execute();
        }
        $this->write();
    }

    /**
     * Mendapatkan object dari alamat. Alamat merupakan nama file dengan
     * susunan hirarki yang menggunakan karakter titik sebagai pemisah.
     * Misalnya:
     *   - entity.type.node.bundle
     *   - entity.type.node.bundle.article
     */
    protected function getObjetFromAddress($name)
    {
        $name = $this->expandAddressName($name);
        $explode = explode('.',$name);
        $odd = true;
        $namespace = '\\Gpl\\Drupal\\' . ucfirst($explode[0]);
        while($part = array_shift($explode)) {
            if ($odd) {
                $object = $namespace . '\\' . ucfirst($part);
            }
            else {
                $method = 'get' . ucfirst($part);
                if (is_string($object)) {
                    $object = $object . '::' . $method;
                }
                else {
                    $object = array($object, $method);
                }
                $args = isset($explode[0]) ? array($explode[0]): array();
                $result = call_user_func_array($object, $args);
                if (is_string($result)) {
                    $namespace = $result;
                }
                elseif (is_object($result)) {
                    $object = $result;
                    array_shift($explode);
                    $odd = $odd ? false: true;
                }
            }
            $odd = $odd ? false: true;
        }
        return is_object($object) ? $object : null;
    }

    /**
     * Menerjemahkan alias dari address.
     * Misalnya: `content` merupakan singkatan dari `entity.type.node.bundle`.
     */
    protected function expandAddressName($name)
    {
        $explode = explode('.',$name);
        switch ($explode[0]) {
            case 'content':
                $explode[0] = 'entity.type.node.bundle';
                return implode('.', $explode);
            case 'user':
                $explode[0] = 'entity.type.user.bundle.user.field';
                return implode('.', $explode);
            case 'term':
                $explode[0] = 'entity.type.taxonomy_term.bundle';
                return implode('.', $explode);
        }
        return $name;
    }
}
