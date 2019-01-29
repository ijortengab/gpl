<?php
namespace Gpl\Drupal\Entity\Node;

use Gpl\Application\Application;
use Gpl\Application\ApplicationInterface;
use Gpl\Drupal\Field\Field;
use Gpl\Drupal\Variable\VariableManager as Variable;

class Node implements ApplicationInterface
{
    const ENTITY_TYPE = 'node';

    /**
     * Menampung instance dari object Node.
     */
    protected static $storage = array();

    protected $is_bundle_new = false;

    /**
     * Berisi entity bundle atau node type.
     */

    protected $bundle_name;

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
     * Saat property $yaml di set, value-nya sudah pasti array karena
     * menggunakan magic (array) saat Parse::Yaml().
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
    public static function getBundle($machine_name)
    {
        if (array_key_exists($machine_name, static::$storage)) {
            return static::$storage[$machine_name];
        }
        static::$storage[$machine_name] = new static($machine_name);
        return static::$storage[$machine_name];
    }

    /**
     * Construct. Fleksibel baik bundle belum didefinisikan, atau bundle belum
     * ada di database.
     */
    public function __construct($bundle_name)
    {
        $this->bundle_name = $bundle_name;
        $node_type = node_type_get_type($bundle_name);
        if ($node_type === false) {
            $this->is_bundle_new = true;
            Application::writeRegister($this);
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
        return $this->bundle_name;
    }

    /**
     *
     */
    public function isBundleNew()
    {
        return $this->is_bundle_new;
    }

    /**
     * Mendapatkan dan autocreate instance Field dengan kemudian menyimpannya
     * dalam property $field. Identifiernya adalah field_name.
     */
    public function getField($field_name)
    {
        if (array_key_exists($field_name, $this->fields)) {
            return $this->fields[$field_name];
        }
        $this->fields[$field_name] = new Field($field_name, $this);
        return $this->fields[$field_name];
    }

    /**
     * Melakukan analisis terkait object ini mau diapakan kedepannya.
     * Kemudian populate property $analyze.
     */
    public function analyze()
    {
        $this->analyze = 'modify_bundle';
        return $this;
    }

    /**
     * Eksekusi class ini mau diapakan kedepannya berdasarkan hasil analyze.
     */
    public function execute()
    {
        switch ($this->analyze) {
            case 'modify_bundle':
                $this->modifyBundle($this->yaml);
                break;
        }
    }

    /**
     * Melakukan aktivitas menulis kedalam database.
     */
    public function write()
    {
        $this->populateProperty();
        return $this->property->write();
    }

    /**
     * Action modify each bundle.
     */
    protected function modifyBundle($info)
    {
        $this->populateProperty();
        $this->property->modify($info);
    }

    /**
     *
     */
    protected function populateProperty()
    {
        if (null === $this->property) {
            $this->property = new NodeProperty($this);
        }
    }
}
