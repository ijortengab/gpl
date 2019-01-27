<?php
namespace Gpl\Drupal\Field;

use Gpl\Application\ApplicationInterface;

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


    /**
     * Memulai instance.
     */
    public function __construct($parent, $field_name = null)
    {
        $this->parent = $parent;
        $this->field_name = $field_name;

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
                $this->modifyField($this->bundle, $this->yaml);
                break;
        }
    }

    /**
     *
     */
    protected function modifyField($machine_name, $info)
    {
    }

    /**
     *
     */
    public function write()
    {

    }
}
