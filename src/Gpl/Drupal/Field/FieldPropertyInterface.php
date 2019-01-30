<?php
namespace Gpl\Drupal\Field;

interface FieldPropertyInterface
{
    public function modify();

    public function write();

    public function getDependencies();
}
