<?php
namespace Gpl\Drupal\Entity;

class Entity
{
    /**
     * Memberikan informasi object baru pada entity tertentu.
     */
    public static function getType($type)
    {
        switch ($type) {
            case 'node':
                return '\\Gpl\\Drupal\\Entity\\Node\\Node';
        }
    }
}
