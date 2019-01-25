<?php
namespace Gpl\Drupal\Entity\Node;

use Gpl\Drupal\Field\Field;
use Gpl\Application\Application;
use Gpl\Drupal\Variable\VariableManager as Variable;

class Node
{
    /**
     * Menampung instance dari object Node.
     */
    protected static $storage = array();

    /**
     * Berisi entity bundle atau node type.
     */
    protected $bundle;

    /**
     * Variable untuk menampung hasil analisis address (nama file YAML).
     * Misalnya: nama file adalah (content.yml), sehingga address adalah
     * `content`. Maka isi dari file YAML pada array dimensi pertama merupkan
     * daftar nama-nama bundle, sehingga value dari property $analyze adalah
     * `modify_bundles`.
     */
    protected $analyze;

    /**
     * Instance dari NodeProperty.
     */
    protected $property;

    /**
     * Array hasil parse Yaml.
     */
    protected $yaml;

    /**
     * Menampung instance dari object Field.
     */
    protected $fields = array();

    /**
     * Mendapatkan dan autocreate instance self dengan kemudian menyimpannya
     * dalam property $storage. Identifiernya adalah entity bundle (node type).
     */
    public static function getBundle($machine_name = null)
    {
        $key = $machine_name === null ? '-' : $machine_name;
        if (array_key_exists($key, static::$storage)) {
            return static::$storage[$key];
        }
        static::$storage[$key] = new static($machine_name);
        return static::$storage[$key];
    }

    /**
     * Construct. Fleksibel baik bundle belum didefinisikan, atau bundle belum
     * ada di database.
     */
    public function __construct($bundle = null)
    {
        if ($bundle == null) {
            return $this;
        }
        $this->bundle = $bundle;
        $node_type = node_type_get_type($bundle);
        if ($node_type === false) {
            $this->property = new NodeProperty;
            Application::writeRegister($this);
        }
        else {
            $this->property = new NodeProperty($node_type);
        }
        return $this;
    }

    /**
     * Set property $yaml.
     */
    public function setYaml($yaml)
    {
        $this->yaml = $yaml;
        return $this;
    }

    /**
     * Memberikan value dari property $bundle.
     */
    public function getBundleName()
    {
        return $this->bundle;
    }

    /**
     * Mendapatkan dan autocreate instance Field dengan kemudian menyimpannya
     * dalam property $field. Identifiernya adalah field_name.
     */
    public function getField($field_name = null)
    {
        if ($field_name === null) {
            return new Field($this);
        }
        if (array_key_exists($field_name, $this->fields)) {
            return $this->fields[$field_name];
        }
        $this->fields[$field_name] = new Field($this, $field_name);
        return $this->fields[$field_name];
    }

    /**
     * Melakukan analisis terkait object ini mau diapakan kedepannya.
     * Kemudian populate property $analyze.
     */
    public function analyze()
    {
        if ($this->bundle === null) {
            $this->analyze = 'modify_bundles';
        }
        else{
            $this->analyze = 'modify_bundle';
        }
        return $this;
    }

    /**
     * Eksekusi class ini mau diapakan kedepannya berdasarkan hasil analyze.
     */
    public function execute()
    {
        switch ($this->analyze) {
            case 'modify_bundles':
                $this->modifyBundles();
                break;
            case 'modify_bundle':
                $this->modifyBundle($this->bundle, $this->yaml);
                break;
        }
    }

    /**
     * Melakukan aktivitas menulis kedalam database.
     */
    public function write()
    {
        return $this->property->write($this);
    }

    /**
     * Action modify bundles.
     */
    protected function modifyBundles()
    {
        foreach ($this->yaml as $key => $value) {
            $this->modifyBundle($key, $value);
        }
    }

    /**
     * Action modify each bundle.
     */
    protected function modifyBundle($machine_name, $info)
    {
        $node = self::getBundle($machine_name);
        $node->property->populate($node, $info);
    }
}
