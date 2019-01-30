<?php
namespace Gpl\Drupal\Entity\TaxonomyTerm;

interface TaxonomyTermPropertyInterface
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
