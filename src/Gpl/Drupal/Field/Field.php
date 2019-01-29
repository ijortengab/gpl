<?php
namespace Gpl\Drupal\Field;

use Gpl\Application\Application;
use Gpl\Application\ApplicationInterface;
use Gpl\Application\Utility;

class Field implements ApplicationInterface
{
    /**
     * Object parent berupa Entity Type, seperti Node, User.
     */
    protected $parent;

    /**
     * Array hasil parse Yaml.
     * Saat property $yaml di set, value-nya sudah pasti array karena
     * menggunakan magic (array) saat Parse::Yaml().
     */
    protected $yaml;

    protected $field_name;

    protected $property;

    /**
     * Memulai instance.
     */
    public function __construct($field_name, $parent)
    {
        $this->field_name = $field_name;
        $this->parent = $parent;
        $field_info = field_info_field($machine_name);
        if (null === $field_info) {
            Application::writeRegister($this);
        }
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
     * Melakukan analisis terkait object ini mau diapakan kedepannya.
     * Kemudian populate property $analyze.
     */
    public function analyze()
    {
        $this->analyze = 'modify_field';
        return $this;
    }

    /**
     * Eksekusi class ini mau diapakan kedepannya berdasarkan hasil analyze.
     */
    public function execute()
    {
        switch ($this->analyze) {
            case 'modify_field':
                $this->modifyField($this->field_name, $this->yaml);
                break;
        }
    }

    /**
     *
     */
    protected function modifyField($machine_name, $info)
    {
        // $this->populateProperty();
        $field_info = field_info_field($machine_name);
        if (null === $field_info) {

        }
    }

    /**
     *
     */
    public function write()
    {
        $this->populateProperty();
        $this->property->write();
        return;
        $field_type = 'text';
        $widget_type = 'text_textfield';
        // Field Basic.
        $field = array(
            'field_name' => $this->field_name,
            'type' => $field_type,
        );
        // Field Settings.
        $field_settings = [
            'cardinality' => 1,
            'settings' => [
                'max_length' => 255,
            ],
        ];
        $field = array_merge($field, $field_settings);
        // Instance Basic.
        $instance = array(
            'field_name' => $this->field_name,
            'entity_type' => $this->parent::ENTITY_TYPE,
            'bundle' => $this->parent->getBundleName(),
            'label' => Utility::createLabel($this->field_name),
            'widget' => [
                'type' => $widget_type,
                'weight' => '-3',
            ],
        );
        // Instance Settings.
        $instance_settings = [
            'required' => 1,
            'description' => '',
            'default_value' => [
                [
                    'value' => 'apakahdemikian',
                ],
            ],
            'settings' => [
                'text_processing' => '0',
            ],
        ];
        $instance = array_merge($instance, $instance_settings);
        // Instance Widget Settings.
        $widget_settings = [
            'settings' => [
                'size' => '60',
            ],
        ];
        $instance['widget'] = array_merge($instance['widget'], $widget_settings);
        // Create.
        field_create_field($field);
        field_create_instance($instance);
    }

    /**
     *
     */
    protected function populateProperty()
    {
        if (null === $this->property) {
            $this->property = new FieldTextProperty;
            // $node_type = node_type_get_type($this->bundle);
            // if ($node_type === false) {
                // $this->property = new NodeProperty;
            // }
            // else {
                // $this->property = new NodeProperty($node_type);
            // }
        }
    }

}
