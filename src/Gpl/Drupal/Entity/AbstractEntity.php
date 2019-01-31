<?php
namespace Gpl\Drupal\Entity;

use Gpl\Drupal\Field\Field;

abstract class AbstractEntity
{
    protected $is_dependencies_fulfilled = false;

    /**
     * Menampung instance dari object EntityInterface.
     */
    protected static $bundles = array();

    /**
     * Berisi entity bundle atau node type.
     */
    protected $bundle_name;


    /**
     * Memberikan informasi bahwa bundle baru dibuat dan belum ada di database.
     */
    protected $is_bundle_new = false;

    /**
     * Menampung instance dari object Field.
     */
    protected $fields = array();

    /**
     * Array hasil parse Yaml.
     * Saat property $yaml di set, value-nya sudah pasti array karena
     * menggunakan magic (array) saat Parse::Yaml().
     */
    protected $info;



    /**
     * {@inheritdoc}
     */
    public function isBundleNew()
    {
        return $this->is_bundle_new;
    }

    /**
     * {@inheritdoc}
     */
    public function getBundleName()
    {
        return $this->bundle_name;
    }

    /**
     * Mendapatkan dan autocreate instance self dengan kemudian menyimpannya
     * dalam property $bundles. Identifiernya adalah entity bundle name.
     */
    public static function getBundle($machine_name)
    {
        if (array_key_exists($machine_name, static::$bundles)) {
            return static::$bundles[$machine_name];
        }
        static::$bundles[$machine_name] = new static($machine_name);
        return static::$bundles[$machine_name];
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
        if ($object = Field::getInstance($field_name, $this)) {
            $this->fields[$field_name] = $object;
            return $this->fields[$field_name];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setInfo($yaml)
    {
        $this->info = $yaml;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     *
     */
    public function isDependenciesFulfilled()
    {
        return $this->is_dependencies_fulfilled;
    }
}
