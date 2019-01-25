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
    protected $submitted = 0;
    protected $options = [];

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
        $modified = false;

        if (array_key_exists('label', $info) && $this->getLabel() != $info['label']) {
            $this->setLabel($info['label']);
            $modified = true;
        }
        if ($modified) {
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
            $this->{$key} = Variable::set('node_' . $key . '_' . $info->type, $this->{$key});
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
}

