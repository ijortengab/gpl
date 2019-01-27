<?php
namespace Gpl\Drupal\Entity\Node;

use Gpl\Application\Application;
use Gpl\Drupal\Variable\VariableManager as Variable;

/**
 * Seluruh property yang ada didalam class ini merupakan property dari entity
 * Node.
 */
class NodeProperty
{
    // Storage: table node_type.
    protected $type;
    protected $name;
    protected $base = 'node_content';
    protected $module;
    protected $description = '';
    protected $help = '';
    protected $has_title = 1;
    protected $title_label = 'Title';
    protected $custom = 1;
    protected $modified = 1;
    protected $locked = 0;
    protected $disabled = 0;
    protected $orig_type;
    // Storage: table variable.
    protected $preview = 0;
    protected $submitted = 1;
    protected $options = ['status'];

    /**
     * Memberikan array berisi nama property default yang disimpan pada
     * table node_type.
     */
    public static function getPropertiesDefault()
    {
        return [
            'type', 'name', 'base', 'module', 'description', 'help',
            'has_title', 'title_label', 'custom', 'modified', 'locked',
            'disabled', 'orig_type',
        ];
    }

    /**
     * Memberikan array berisi nama property tambahan yang disimpan pada
     * table variable.
     */
    public static function getPropertiesVariable()
    {
        return ['preview', 'submitted', 'options'];
    }

    /**
     * Memulai instance.
     */
    public function __construct($existing = null)
    {
        if ($existing === null) {
            return;
        }
        foreach (static::getPropertiesDefault() as $key) {
            $this->{$key} = $existing->{$key};
        }
        foreach (static::getPropertiesVariable() as $key) {
            $this->{$key} = Variable::get('node_' . $key . '_' . $existing->type);
        }
    }

    /**
     * Mengeset secara massal berbagai property yang didapat dari hasil parse
     * file Yaml.
     */
    public function populate($parent, $info)
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
            Application::writeRegister($parent);
        }
    }

    /**
     * Melakukan aktivitas menulis kedalam database.
     */
    public function write($parent)
    {
        $info = (object) [];
        foreach (static::getPropertiesDefault() as $key) {
            $info->{$key} = $this->{$key};
        }
        // Property $type harus ada.
        if (!isset($info->type)) {
            $info->type = $parent->getBundleName();
        }
        // Property $name harus ada.
        if (!isset($info->name)) {
            $info->name = preg_replace_callback("/(_)(.)/", function ($matches) {
                return ' ' . strtoupper($matches[2]);
            }, ucfirst($info->type));;
        }
        node_type_save($info);
        foreach (static::getPropertiesVariable() as $key) {
            Variable::set('node_' . $key . '_' . $info->type, $this->{$key});
        }
    }

    /**
     * Mengeset label dari entity.
     */
    protected function setLabel($value)
    {
        $this->name = $value;
    }

    /**
     * Mendapatkan entity label.
     */
    protected function getLabel()
    {
        return $this->name;
    }

    protected function getProperty($name)
    {
        switch ($name) {
            case 'preview':
                switch ($this->preview) {
                    case 0: return 'disabled';
                    case 1: return 'optional';
                    case 2: return 'required';
                }
                break;

            case 'guidelines':
                return $this->help;

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
                    if (in_array($unreadable_option, $this->options)) {
                        $return_value[] = $readable_option;
                    }
                }
                sort($return_value); // Required.
                return $return_value;
                break;

            case 'display_options':
                $return_value = [];
                if ($this->submitted === 1) {
                    $return_value[] = 'author_date';
                }
                sort($return_value); // Required.
                return $return_value;
                break;

            default:
                if (property_exists($this, $name)) {
                    return $this->{$name};
                }
                break;
        }
    }

    protected function setProperty($name, $value)
    {
        switch ($name) {
            case 'title_label':
            case 'description':
                $this->{$name} = $value;
                break;

            case 'preview':
                switch ($value) {
                    case 'disabled': $this->preview = 0; break;
                    case 'optional': $this->preview = 1; break;
                    case 'required': $this->preview = 2; break;
                }
                break;

            case 'guidelines':
                $this->help = $value;
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
                $this->options = $original_value;
                break;

            case 'display_options':
                // Convert null to array.
                $value = (array) $value;
                $this->submitted = in_array('author_date', $value) ? 1 : 0;
                break;
        }
    }
}
