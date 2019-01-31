<?php
namespace Gpl\Drupal\Field;

class FieldValidation
{
    /**
     * Reserved field name.
     * Source from hook_entity_info() in key array "entity keys".
     */
    public static $reserved_field_name = [
        // Node.
        'nid', 'vid', 'type', 'title', 'language',
        // Taxonomy.
        'tid', 'vocabulary_machine_name', 'name',
        // File.
        'fid', 'filename',
        // Taxonomy Vocabulary.
        'vid', 'name',
        // User.
        'uid',
    ];
}
