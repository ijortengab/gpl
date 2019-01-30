<?php
namespace Gpl\Drupal\Field;

use Gpl\Application\Utility;

class FieldPropertyText extends AbstractFieldProperty implements FieldPropertyInterface
{
    const FIELD_TYPE = 'text';

    const FIELD_WIDGET = 'text_textfield';

    /**
     *
     */
    public function getDependencies()
    {
        return [];
    }

    protected function getTableFieldConfigProperties()
    {
        return [
            // Basic.
            'field_name' => $this->parent->getFieldName(),
            'type' => $this::FIELD_TYPE,
            'cardinality' => 1,
            // Widget.
            'settings' => [
                'max_length' => 255,
            ],
        ];
    }

    protected function getTableFieldInstanceProperties()
    {
        return [
            // Basic.
            'field_name' => $this->parent->getFieldName(),
            'entity_type' => $this->parent->getParentEntity()::ENTITY_TYPE,
            'bundle' => $this->parent->getParentEntity()->getBundleName(),
            'label' => Utility::createLabel($this->parent->getFieldName()),
            'widget' => [
                'type' => $this::FIELD_WIDGET,
                'weight' => '-3',
            ],
            // Settings.
            'required' => 1,
            'description' => '',
            'default_value' => [
                [
                    'value' => '',
                ],
            ],
            'settings' => [
                'text_processing' => '0',
                // Widget Settings.
                'size' => '60',
            ],
        ];
    }
}
