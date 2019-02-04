<?php
namespace Gpl\Drupal\Field;

use Gpl\Application\Application;
use Gpl\Application\ApplicationInterface;
use Gpl\Drupal\Entity\EntityInterface;

class Field implements ApplicationInterface, FieldInterface
{
    /**
     *
     */
    protected $is_field_new = false;

    protected $is_field_instance_new = false;

    /**
     * String berupa nama field dalam format machine name.
     * Populated by __construct().
     */
    protected $field_name;

    /**
     * String berupa type field. Contoh: text, image.
     * Populated by __construct(). Jika Field belum dibuat, maka nilai-nya null.
     */
    protected $type;

    /**
     * Array hasil parse Yaml.
     * Saat property $yaml di set, value-nya sudah pasti array karena
     * menggunakan magic (array) saat Parse::Yaml().
     */
    protected $yaml;

    protected $dependencies;

    /**
     * Object FieldPropertyInterface().
     */
    protected $property;

    /**
     * Object EntityInterface().
     */
    protected $parent;

    /**
     *
     */
    public static function getInstance($field_name, EntityInterface $parent)
    {
        if (in_array($field_name, FieldValidation::$reserved_field_name)) {
            // todo. set log.
        }
        else {
            return new static($field_name, $parent);
        }
    }

    /**
     * Memulai instance.
     */
    public function __construct($field_name, EntityInterface $parent)
    {
        $this->field_name = $field_name;
        $this->parent = $parent;
        $field_info = field_info_field($field_name);
        if (null === $field_info) {
            $this->is_field_new = true;
            Application::getEventDispatcher()->addListener(Application::WRITE, [$this, 'write']);
        }
        else {
            $this->type = $field_info['type'];
        }
        $field_instance = field_info_instance($this->parent->getEntityType(), $field_name, $this->parent->getBundleName());
        if (null === $field_instance) {
            $this->is_field_instance_new = true;
            Application::getEventDispatcher()->addListener(Application::WRITE, [$this, 'write']);
        }
        else {
            // todo.
        }
        return $this;
    }

    /**
     *
     */
    public function getFieldName()
    {
        return $this->field_name;
    }

    /**
     *
     */
    public function getParentEntity()
    {
        return $this->parent;
    }

    /**
     *
     */
    public function isFieldNew()
    {
        return $this->is_field_new;
    }

    /**
     *
     */
    public function isFieldInstanceNew()
    {
        return $this->is_field_instance_new;
    }

    /**
     * Set property $yaml.
     */
    public function setInfo($yaml)
    {
        $this->yaml = $yaml;
        return $this;
    }

    /**
     * Melakukan analisis terkait object ini mau diapakan kedepannya.
     * Kemudian populate property $analyze.
     */
    public function analyze()
    {
        $this->analyze = 'modify_field';
        return $this;
    }

    /**
     *
     */
    public function getInfo()
    {
        return $this->yaml;
    }

    /**
     * Eksekusi class ini mau diapakan kedepannya berdasarkan hasil analyze.
     */
    public function execute()
    {
        switch ($this->analyze) {
            case 'modify_field':
                $this->populateProperty();
                $this->property->modify();
                break;
        }
    }

    /**
     *
     */
    public function write()
    {
        $this->populateProperty();
        $this->property->write();
    }

    /**
     *
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     *
     */
    protected function populateProperty()
    {
        if (null === $this->property) {
            // Property berdasarkan type. Jika $type null artinya field
            // belum exists. Populate $type jika ada informasi di Yaml.
            if ($this->type === null) {
                if (isset($this->yaml['type'])) {
                    $this->type = $this->yaml['type'];
                }
                else {
                    // Tidak ada info di Yaml, artinya secara default adalah text.
                    $this->type = 'text';
                }
            }
            switch ($this->type) {
                case 'image':
                    $this->property = new FieldPropertyImage($this);
                    break;

                case 'text':
                    $this->property = new FieldPropertyText($this);
                    break;

                default:
                    die('Todo.'); // Todo. Urus ini.
                    break;
            }
            $this->setDependencies($this->property->getDependencies());
        }
    }

    /**
     *
     */
    protected function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;
    }
}
