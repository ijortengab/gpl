<?php
namespace Gpl\Drupal\Field;

use Gpl\Application\Application;

abstract class AbstractFieldProperty
{
    protected $is_property_table_field_config_modified = false;

    protected $is_property_table_field_instance_modified = false;

    protected $property_table_field_config;

    protected $property_table_field_instance;

    protected $parent;

    abstract protected function getTableFieldConfigProperties();

    abstract protected function getTableFieldInstanceProperties();

    public function __construct(FieldInterface $parent)
    {
        $this->parent = $parent;
        $this->property_table_field_config = $this->getTableFieldConfigProperties();
        $this->property_table_field_instance = $this->getTableFieldInstanceProperties();
        if ($this->parent->isFieldNew()) {
            $this->is_property_table_field_config_modified = true;
        }
        else {
            $field_source = field_info_field($this->parent->getFieldName());
            $this->property_table_field_config = array_merge($this->property_table_field_config, $field_source);
        }
        if ($this->parent->isFieldInstanceNew()) {
            $this->is_property_table_field_instance_modified = true;
        }
        else {
            $instance_source = field_read_instance($this->parent->getParentEntity()::ENTITY_TYPE, $this->parent->getFieldName(), $this->parent->getParentEntity()->getBundleName());
            $this->property_table_field_instance = array_merge($this->property_table_field_instance, $instance_source);
        }
    }

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
            }
        }
        if ($is_modified) {
            Application::writeRegister($this->parent);
        }
    }

    /**
     *
     */
    public function write()
    {
        if ($this->is_property_table_field_config_modified) {
            if ($this->parent->isFieldNew()) {
                field_create_field($this->property_table_field_config);
            }
            else {
                // Warning: perlu perhatian pada data yang exists.
                $field_source = field_info_field($this->parent->getFieldName());
                $field = array_merge($field_source, $this->property_table_field_config);
                try {
                    field_update_field($field);
                }
                catch (Exception $e) {
                    // drupal_set_message(t('Attempt to update field %label failed: %message.', array('%label' => $instance['label'], '%message' => $e->getMessage())), 'error');
                }
            }
        }
        if ($this->is_property_table_field_instance_modified) {
            if ($this->parent->isFieldInstanceNew()) {
                field_create_instance($this->property_table_field_instance);
            }
            else {
                $instance_source = field_read_instance($this->parent->getParentEntity()::ENTITY_TYPE, $this->parent->getFieldName(), $this->parent->getParentEntity()->getBundleName());
                $instance = array_merge($instance_source, $this->property_table_field_instance);
                field_update_instance($instance);
            }
        }
    }

    /**
     *
     */
    protected function getLabel()
    {
        return $this->property_table_field_instance['label'];
    }

    /**
     *
     */
    protected function setLabel($value)
    {
        $this->property_table_field_instance['label'] = $value;
        $this->is_property_table_field_instance_modified = true;
    }
}
