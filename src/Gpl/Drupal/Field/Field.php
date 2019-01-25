<?php
namespace Gpl\Drupal\Field;

class Field
{
    /**
     * Object parent berupa Entity Type, seperti Node, User.
     */
    protected $parent;

    /**
     * Memulai instance.
     */
    public function __construct($parent, $field_name = null)
    {
        $this->parent = $parent;
    }
}
