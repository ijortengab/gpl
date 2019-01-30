<?php
namespace Gpl\Drupal\Field;

interface FieldInterface
{
    public function isFieldNew();

    public function isFieldInstanceNew();

    public function getFieldName();

    public function getParentEntity();

    public function getInfo();
}
