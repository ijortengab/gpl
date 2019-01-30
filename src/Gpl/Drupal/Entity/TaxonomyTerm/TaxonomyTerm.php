<?php
namespace Gpl\Drupal\Entity\TaxonomyTerm;

use Gpl\Application\Application;
use Gpl\Application\ApplicationInterface;
use Gpl\Drupal\Field\Field;
use Gpl\Drupal\Entity\EntityInterface;

class TaxonomyTerm implements ApplicationInterface, EntityInterface
{
    const ENTITY_TYPE = 'taxonomy_term';

    /**
     * Menampung instance dari object TaxonomyTerm.
     */
    protected static $storage = array();

    /**
     * Memberikan informasi bahwa bundle baru dibuat dan belum ada di database.
     */
    protected $is_bundle_new = false;

    protected $is_dependencies_fulfilled = false;

    /**
     * Berisi entity bundle atau node type.
     */
    protected $bundle_name;

    /**
     * Hasil analyze().
     */
    protected $analyze;

    /**
     * Instance dari TaxonomyTermPropertyInterface().
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
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return[
            'module' => ['taxonomy'],
        ];
    }

    /**
     * Construct. Fleksibel baik bundle belum didefinisikan, atau bundle belum
     * ada di database.
     */
    public function __construct($bundle_name)
    {
        $this->bundle_name = $bundle_name;
        $is_dependencies_fulfilled = $this->is_dependencies_fulfilled = Application::analyzeDependencies($this->getDependencies());

        $new = false;
        if ($is_dependencies_fulfilled === false) {
            $new = true;
        }
        elseif (taxonomy_vocabulary_machine_name_load($bundle_name) === false) {
            $new = true;
        }
        if ($new) {
            $this->is_bundle_new = true;
            Application::writeRegister($this);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setInfo($yaml)
    {
        $this->yaml = $yaml;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getInfo()
    {
        return $this->yaml;
    }

    /**
     * {@inheritdoc}
     */
    public function getBundleName()
    {
        return $this->bundle_name;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function analyze()
    {
        $this->analyze = 'modify_bundle';
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        switch ($this->analyze) {
            case 'modify_bundle':
                $this->populateProperty();
                $this->property->modify();
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write()
    {
        $this->populateProperty();
        return $this->property->write();
    }

    /**
     *
     */
    public function isDependenciesFulfilled()
    {
        return $this->is_dependencies_fulfilled;
    }

    /**
     * Filled $property.
     */
    protected function populateProperty()
    {
        if (null === $this->property) {
            $this->setProperty(new TaxonomyTermProperty($this));
        }
    }

    /**
     * Mengeset $property dengan TaxonomyTermPropertyInterface().
     */
    protected function setProperty(TaxonomyTermPropertyInterface $property)
    {
        $this->property = $property;
    }
}
