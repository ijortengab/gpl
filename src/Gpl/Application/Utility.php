<?php
namespace Gpl\Application;

/**
 * Class yang menyediakan fungsi static yang bisa digunakan untuk berbagai
 * keperluan.
 */
class Utility
{
    /**
     * Memberikan Label berdasarkan machine_name.
     * Contoh: "my_blog" menjadi "My Blog'.
     */
    public static function createLabel($machine_name)
    {
        return preg_replace_callback("/(_)(.)/", function ($matches) {
            return ' ' . strtoupper($matches[2]);
        }, ucfirst($machine_name));
    }

    /**
     * Untuk keperluan debug.
     */
    public static function getInfoObject($object)
    {
        if (null === $object) {
            return;
        }
        $get_class = get_class($object);
        switch ($get_class) {
            case 'Gpl\\Drupal\\Field\\Field':
                $json = [
                    'name' => $object->getFieldName(),
                    'parent' => $object->getParentEntity()::ENTITY_TYPE . '.' . $object->getParentEntity()->getBundleName(),
                ];
                return $get_class . json_encode($json);

            case 'Gpl\\Drupal\\Entity\\Node\\Node':
            case 'Gpl\\Drupal\\Entity\\TaxonomyTerm\\TaxonomyTerm':
                $json = [
                    'bundle' => $object->getBundleName(),
                ];
                return $get_class . json_encode($json);

            default:
                return $get_class;
        }
    }
}
