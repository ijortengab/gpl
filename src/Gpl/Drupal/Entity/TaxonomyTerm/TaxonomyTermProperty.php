<?php
namespace Gpl\Drupal\Entity\TaxonomyTerm;

use Gpl\Application\Application;
use Gpl\Application\Utility;
use Gpl\Drupal\Entity\EntityInterface;

/**
 * Seluruh property yang ada didalam class ini merupakan property dari entity
 * TaxonomyTerm.
 */
class TaxonomyTermProperty implements TaxonomyTermPropertyInterface
{
    /**
     * Flag bahwa perlu telah dilakukan perubahan pada $property_table_taxonomy.
     */
    protected $is_property_table_taxonomy_modified = false;

    /**
     * Berisi array yang akan digunakan sebagai argument pada fungsi
     * taxonomy_vocabulary_save().
     */
    protected $property_table_taxonomy;

    /**
     * Object dari EntityInterface.
     */
    protected $parent;

    /**
     * Memulai instance.
     */
    public function __construct(EntityInterface $parent)
    {
        $this->parent = $parent;
        $this->property_table_taxonomy = $this->getTableTaxonomyTermProperties();

        if ($this->parent->isDependenciesFulfilled() === false) {
            $this->is_property_table_taxonomy_modified = true;
        }
        elseif ($this->parent->isBundleNew()) {
            $this->is_property_table_taxonomy_modified = true;
        }
        else {
            $bundle_name = $this->parent->getBundleName();
            $vocabulary = taxonomy_vocabulary_machine_name_load($bundle_name);
            foreach (array_keys($this->getTableTaxonomyTermProperties()) as $key) {
                $this->property_table_taxonomy[$key] = $vocabulary->{$key};
            }
        }
    }

    /**
     * Mengeset secara massal berbagai property yang didapat dari hasil parse
     * file Yaml.
     */
    public function modify()
    {
        $info = $this->parent->getInfo();
        $is_modified = false;
        $keys = array_keys($info);

        while ($key = array_shift($keys)) {
            switch ($key) {
                case 'label':
                    if ($this->getLabel() != $info['label']) {
                        $this->setLabel($info['label']);
                        $is_modified = true;
                    }
                    break;

                case 'field':
                    // Do something.
                    break;

                default:
                    if (is_array($info[$key])) {
                        sort($info[$key]); // Required.
                    }
                    if ($this->getProperty($key) != $info[$key]) {
                        $this->setProperty($key, $info[$key]);
                        $is_modified = true;
                    }
                    break;
            }
        }
        if ($is_modified) {
            Application::getEventDispatcher()->addListener(Application::WRITE, [$this->parent, 'write']);
        }
    }

    /**
     * Melakukan aktivitas menulis kedalam database.
     */
    public function write()
    {
        if ($this->is_property_table_taxonomy_modified) {
            $this->is_property_table_taxonomy_modified = false;
            $info = (object) [];
            foreach (array_keys($this->getTableTaxonomyTermProperties()) as $key) {
                $info->{$key} = $this->property_table_taxonomy[$key];
            }

            // Property $machine_name harus ada.
            if (!isset($info->machine_name)) {
                $info->machine_name = $this->parent->getBundleName();
            }
            // Property $name harus ada.
            if (!isset($info->name)) {
                $info->name = Utility::createLabel($info->machine_name);
            }
            taxonomy_vocabulary_save($info);
        }
    }

    /**
     * Memberikan array berisi nama property default yang disimpan pada
     * table taxonomy.
     */
    protected function getTableTaxonomyTermProperties()
    {
        return [
            'vid' => null,
            'name' => null,
            'machine_name' => null,
            'description' => null,
            'hierarchy' => 0,
            'module' => null,
            'weight' => 0,
        ];
    }

    /**
     * Mengeset label dari entity.
     */
    protected function setLabel($value)
    {
        $this->property_table_taxonomy['name'] = $value;
        $this->is_property_table_taxonomy_modified = true;
    }

    /**
     * Mendapatkan entity label.
     */
    protected function getLabel()
    {
        return $this->property_table_taxonomy['name'];
    }

    protected function getProperty($name)
    {
        switch ($name) {
            default:
                if (array_key_exists($name, $this->property_table_taxonomy)) {
                    return $this->property_table_taxonomy[$name];
                }
                break;
        }
    }

    protected function setProperty($name, $value)
    {
        switch ($name) {
            case 'description':
                $this->property_table_taxonomy[$name] = $value;
                $this->is_property_table_taxonomy_modified = true;
                break;
        }
    }
}
