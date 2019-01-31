<?php
namespace Gpl\Drupal\Entity;

interface EntityInterface
{
    /**
     * Memberikan informasi entity_type.
     */
    public function getEntityType();

    /**
     * Memberikan informasi bundle_name.
     */
    public function getBundleName();

    /**
     * Memberikan informasi bahwa bundle belum ada didatabase.
     */
    public function isBundleNew();

    /**
     *
     */
    public function isDependenciesFulfilled();
}
