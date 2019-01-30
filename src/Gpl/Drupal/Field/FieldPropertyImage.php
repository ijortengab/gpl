<?php
namespace Gpl\Drupal\Field;

use Gpl\Application\Utility;

class FieldPropertyImage extends AbstractFieldProperty implements FieldPropertyInterface
{
    const FIELD_TYPE = 'image';

    const FIELD_WIDGET = 'image_image';

    /**
     *
     */
    public function getDependencies()
    {
        return[
            'module' => ['image'],
        ];
    }

    /**
     *
     */
    protected function getTableFieldConfigProperties()
    {
        return [
            // Basic.
            'field_name' => $this->parent->getFieldName(),
            'type' => $this::FIELD_TYPE,
            'cardinality' => 1,
            // Widget.
            'settings' => [
                'uri_scheme' => 'public',
                'default_image' => 0,
            ],
        ];
    }

    /**
     *
     */
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
                'module' => 'image',
                'active' => 1,
                'weight' => '-2',
                'settings' => [
                    'progress_indicator' => 'throbber',
                    'preview_image_style' => 'thumbnail',
                ],
            ],
            // Settings.
            'required' => 0,
            'description' => '',
            'settings' => [
                'file_directory' => '',
                'file_extensions' => 'png gif jpg jpeg',
                'max_filesize' => '',
                'max_resolution' => '',
                'min_resolution' => '',
                'alt_field' => 0,
                'title_field' => 0,
                'default_image' => 0, // fid.
            ],
        ];
    }
}
