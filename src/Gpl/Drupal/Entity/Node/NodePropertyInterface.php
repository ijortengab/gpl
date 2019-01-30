<?php
namespace Gpl\Drupal\Entity\Node;

interface NodePropertyInterface
{
    /**
     * Modifikasi property.
     */
    public function modify();

    /**
     * Menulis kedatabase.
     */
    public function write();
}
