<?php
namespace Gpl\Application;

use Gpl\Data\Config\ConfigManager;
use Gpl\Data\Content\ContentManager;
use Gpl\Data\Structure\StructureManager;
use Gpl\Drupal\Bootstrap\Bootstrap;
use Symfony\Component\Yaml\Yaml;

class Application
{
    /**
     * Array untuk menampung object yang akan melakukan update informasi
     * kedalam database.
     */
    protected static $write_register = [];

    /**
     * Antrian hasil melihat address dari file `.yml`.
     */
    protected static $queue = [];

    /**
     * Antrian baru yang dibuat oleh ApplicationInterface::execute().
     */
    protected static $new_queue = [];

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
    public static function writeRegister(ApplicationInterface $object)
    {
        // Hindari duplikat.
        if (!in_array($object, self::$write_register)) {
            self::$write_register[] = $object;
        }
    }

    /**
     *
     */
    public static function analyzeDependencies($info, $return_solution = false)
    {
        $solution = [];
        $info = array_merge_recursive(['module' => []], $info);
        foreach ($info['module'] as $module) {
            if (!module_exists($module)) {
                switch ($return_solution) {
                    case false:
                        return false;
                    default:
                        $solution['module_enable'][] = $module;
                        break;
                }
            }
        }
        if ($return_solution) {
            return $solution;
        }
        return true;
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
    protected function write()
    {
        $list = self::$write_register;
        // Collecting dependencies.
        $dependencies = ['module' => []];
        foreach ($list as $object) {
            $dependencies = array_merge_recursive($dependencies, $object->getDependencies());
        }
        $module_need_enable = [];
        foreach ($dependencies['module'] as $module) {
            if (!module_exists($module)) {
                $module_need_enable[] = $module;
            }
        }
        if (!empty($module_need_enable)) {
            try {
                if (!module_enable($module_need_enable)) {
                    // todo.
                }
            }
            catch (Exception $e) {
                // todo.
            }
        }

        // Write to Database.
        foreach ($list as $object) {
            $object->write();
        }
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
            $address = $info->name;
            $address = $this->expandAddressName($address);
            $contents = file_get_contents($path);
            $yaml = (array) Yaml::parse($contents);
            static::$queue[] = ['address' => $address, 'yaml' => $yaml];
        }
        do {
            static::$queue = array_merge(static::$queue, static::$new_queue);
            static::$new_queue = [];
            foreach (static::$queue as $each) {
                $object = $this->getObjetFromAddress($each['address'], $each['yaml']);
                if ($object instanceof ApplicationInterface) {
                    $object->setInfo($each['yaml'])->analyze()->execute();
                }
            }
            static::$queue = [];
        }
        while (!empty(static::$new_queue));
        $this->write();
        $drupal_messages = drupal_get_messages();
    }

    /**
     * Mendapatkan object dari alamat. Alamat merupakan nama file dengan
     * susunan hirarki yang menggunakan karakter titik sebagai pemisah.
     * Misalnya:
     *   - entity.type.node.bundle
     *   - entity.type.node.bundle.article
     * todo: Gunakan getObjetFromAddress($name): ApplicationInterface
     * return: null, string atau object. jika string, maka berarti ada
     * penambahan queue. jika null berarti nama field_name tidak valid.
     */
    protected function getObjetFromAddress($name, $yaml)
    {
        // Convert null if exists to empty array.
        $yaml = (array) $yaml;
        $explode = $parts = explode('.',$name);
        $part = array_shift($parts);
        $part = ucfirst($part);
        $object = '\\Gpl\\Drupal\\' . $part . '\\' . $part;
        while($part = array_shift($parts)) {
            $method = 'get' . ucfirst($part);
            $args = isset($parts[0]) ? array(array_shift($parts)): array();
            if (empty($args)) {
                $keys = array_keys($yaml);
                foreach ($keys as $key) {
                    $new_address = implode('.', $explode) . '.' . $key;
                    static::$new_queue[] = ['address' => $new_address, 'yaml' => $yaml[$key]];
                }
            }
            else{
                if (is_string($object)) {
                    $callback = $object . '::' . $method;
                }
                else {
                    $callback = array($object, $method);
                }
                $object = call_user_func_array($callback, $args);
            }
        }
        return $object;
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
            case 'vocabulary':
                $explode[0] = 'entity.type.taxonomy_term.bundle';
                return implode('.', $explode);
        }
        return $name;
    }
}
