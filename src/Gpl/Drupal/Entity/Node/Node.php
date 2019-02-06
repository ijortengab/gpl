<?php
namespace Gpl\Drupal\Entity\Node;

use Gpl\Application\Application;
use Gpl\Application\ApplicationInterface;
use Gpl\Drupal\Entity\EntityInterface;
use Gpl\Drupal\Entity\AbstractEntity;

class Node extends AbstractEntity implements ApplicationInterface, EntityInterface
{
    const ENTITY_TYPE = 'node';

    protected $is_written = false;

    /**
     * Hasil analyze().
     */
    protected $analyze;

    /**
     * Instance dari NodePropertyInterface().
     */
    protected $property;

    /**
     * Construct. Fleksibel baik bundle belum didefinisikan, atau bundle belum
     * ada di database.
     */
    public function __construct($bundle_name)
    {
        $this->bundle_name = $bundle_name;
        $this->is_dependencies_fulfilled = true;
        $node_type = node_type_get_type($bundle_name);
        if ($node_type === false) {
            $this->is_bundle_new = true;
            Application::getEventDispatcher()->addListener(Application::WRITE, [$this, 'write']);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function analyze()
    {
        $this->analyze = 'modify_bundle';
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        switch ($this->analyze) {
            case 'modify_bundle':
                $this->populateProperty();
                $this->property->modify();
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write()
    {
        // Prevent duplicate.
        if ($this->is_written === false) {
            $this->is_written = true;
            $this->populateProperty();
            $this->property->write();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [];
    }

    /**
     *
     */
    public function getEntityType()
    {
        return static::ENTITY_TYPE;
    }

    /**
     * Filled $property.
     */
    protected function populateProperty()
    {
        if (null === $this->property) {
            $this->setProperty(new NodeProperty($this));
        }
    }

    /**
     * Mengeset $property dengan NodePropertyInterface().
     */
    protected function setProperty(NodePropertyInterface $property)
    {
        $this->property = $property;
    }
}
