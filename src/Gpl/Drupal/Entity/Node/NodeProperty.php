<?php
namespace Gpl\Drupal\Entity\Node;

use Gpl\Application\Application;
use Gpl\Application\Utility;
use Gpl\Drupal\Variable\VariableManager as Variable;

/**
 * Seluruh property yang ada didalam class ini merupakan property dari entity
 * Node.
 */
class NodeProperty
{
    protected $parent;

    // Storage: table node_type.
    protected $property_table_node;

    protected $is_property_table_node_modified = false;

    // Storage: table variable.
    protected $property_table_variable;

    protected $is_property_table_variable_modified = false;

    /**
     * Memberikan array berisi nama property default yang disimpan pada
     * table node_type.
     */
    protected function getTableNodeProperties()
    {
        return [
            'type' => null,
            'name' => null,
            'base' => 'node_content',
            'module' => null,
            'description' => '',
            'help' => '',
            'has_title' => 1,
            'title_label' => 'Title',
            'custom' => 1,
            'modified' => 1,
            'locked' => 0,
            'disabled' => 0,
            'orig_type' => null,
        ];
    }

    /**
     * Memberikan array berisi nama property tambahan yang disimpan pada
     * table variable.
     */
    protected function getTableVariablesProperties()
    {
        return [
            'preview' => 0,
            'submitted' => 0,
            'options' => [],
        ];
    }

    /**
     * Memulai instance.
     */
    public function __construct($parent)
    {
        $this->parent = $parent;
        $this->property_table_node = $this->getTableNodeProperties();
        $this->property_table_variable = $this->getTableVariablesProperties();
        if ($this->parent->isBundleNew()) {
            $this->is_property_table_node_modified = true;
            $this->is_property_table_variable_modified = true;
        }
        else {
            $bundle_name = $this->parent->getBundleName();
            $node_type = node_type_get_type($bundle_name);
            foreach (array_keys($this->getTableNodeProperties()) as $key) {
                $this->property_table_node[$key] = $node_type->{$key};
            }
            foreach (array_keys($this->getTableVariablesProperties()) as $key) {
                $this->property_table_variable[$key] = variable_get('node_' . $key . '_' . $node_type->type);
            }
        }

    }

    /**
     * Mengeset secara massal berbagai property yang didapat dari hasil parse
     * file Yaml.
     */
    public function modify($info)
    {
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
            Application::writeRegister($this->parent);
        }
    }

    /**
     * Melakukan aktivitas menulis kedalam database.
     */
    public function write()
    {
        if ($this->is_property_table_node_modified) {
            $this->is_property_table_node_modified = false;
            $info = (object) [];
            foreach (array_keys($this->getTableNodeProperties()) as $key) {
                $info->{$key} = $this->property_table_node[$key];
            }
            // Property $type harus ada.
            if (!isset($info->type)) {
                $info->type = $this->parent->getBundleName();
            }
            // Property $name harus ada.
            if (!isset($info->name)) {
                $info->name = Utility::createLabel($info->type);
            }
            node_type_save($info);
        }
        if ($this->is_property_table_variable_modified) {
            $this->is_property_table_variable_modified = false;
            foreach (array_keys($this->getTableVariablesProperties()) as $key) {
                variable_set('node_' . $key . '_' . $this->parent->getBundleName(), $this->property_table_variable[$key]);
            }
        }
    }

    /**
     * Mengeset label dari entity.
     */
    protected function setLabel($value)
    {
        $this->property_table_node['name'] = $value;
        $this->is_property_table_node_modified = true;
    }

    /**
     * Mendapatkan entity label.
     */
    protected function getLabel()
    {
        return $this->property_table_node['name'];
    }

    protected function getProperty($name)
    {
        switch ($name) {
            case 'preview':
                switch ($this->property_table_variable['preview']) {
                    case 0: return 'disabled';
                    case 1: return 'optional';
                    case 2: return 'required';
                }
                break;

            case 'guidelines':
                return $this->property_table_node['help'];

            case 'default_options':
                // Unreadable => Readable.
                $adapter = [
                    'status' => 'published',
                    'promote' => 'promoted',
                    'sticky' => 'sticky',
                    'revision' => 'revision',
                ];
                $return_value = [];
                foreach ($adapter as $unreadable_option => $readable_option) {
                    if (in_array($unreadable_option, $this->property_table_variable['options'])) {
                        $return_value[] = $readable_option;
                    }
                }
                sort($return_value); // Required.
                return $return_value;
                break;

            case 'display_options':
                $return_value = [];
                if ($this->property_table_variable['submitted'] === 1) {
                    $return_value[] = 'author_date';
                }
                sort($return_value); // Required.
                return $return_value;
                break;

            default:
                if (array_key_exists($name, $this->property_table_node)) {
                    return $this->property_table_node[$name];
                }
                elseif (array_key_exists($name, $this->property_table_variable)) {
                    return $this->property_table_variable[$name];
                }
                break;
        }
    }

    protected function setProperty($name, $value)
    {
        switch ($name) {
            case 'title_label':
            case 'description':
                $this->property_table_node[$name] = $value;
                $this->is_property_table_node_modified = true;
                break;

            case 'preview':
                switch ($value) {
                    case 'disabled': $this->property_table_variable['preview'] = 0; break;
                    case 'optional': $this->property_table_variable['preview'] = 1; break;
                    case 'required': $this->property_table_variable['preview'] = 2; break;
                }
                $this->is_property_table_variable_modified = true;
                break;

            case 'guidelines':
                $this->property_table_node['help'] = $value;
                $this->is_property_table_node_modified = true;
                break;

            case 'default_options':
                // Convert null to array.
                $value = (array) $value;
                // Unreadable => Readable.
                $adapter = [
                    'status' => 'published',
                    'promote' => 'promoted',
                    'sticky' => 'sticky',
                    'revision' => 'revision',
                ];
                $original_value = [];
                foreach ($adapter as $unreadable_option => $readable_option) {
                    if (in_array($readable_option, $value)) {
                        $original_value[] = $unreadable_option;
                    }
                }
                $this->property_table_variable['options'] = $original_value;
                $this->is_property_table_variable_modified = true;
                break;

            case 'display_options':
                // Convert null to array.
                $value = (array) $value;
                $this->property_table_variable['submitted'] = in_array('author_date', $value) ? 1 : 0;
                $this->is_property_table_variable_modified = true;
                break;
        }
    }
}
