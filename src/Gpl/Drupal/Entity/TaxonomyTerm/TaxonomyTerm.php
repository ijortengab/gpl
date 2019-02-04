<?php
namespace Gpl\Drupal\Entity\TaxonomyTerm;

use Gpl\Application\Application;
use Gpl\Application\ApplicationEvent;
use Gpl\Application\ApplicationInterface;
use Gpl\Drupal\Entity\EntityInterface;
use Gpl\Drupal\Entity\AbstractEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TaxonomyTerm extends AbstractEntity implements ApplicationInterface, EntityInterface, EventSubscriberInterface
{
    const ENTITY_TYPE = 'taxonomy_term';

    /**
     * Hasil analyze().
     */
    protected $analyze;

    /**
     * Instance dari TaxonomyTermPropertyInterface().
     */
    protected $property;

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [Application::DEPENDENCIES => 'setDependencies'];
    }

    /**
     * Construct. Fleksibel baik bundle belum didefinisikan, atau bundle belum
     * ada di database.
     */
    public function __construct($bundle_name)
    {
        Application::getEventDispatcher()->addSubscriber($this);
        $this->bundle_name = $bundle_name;
        $is_dependencies_fulfilled = $this->is_dependencies_fulfilled = Application::analyzeDependencies($this->getDependencies());

        $new = false;
        if ($is_dependencies_fulfilled === false) {
            $new = true;
        }
        elseif (taxonomy_vocabulary_machine_name_load($bundle_name) === false) {
            $new = true;
        }
        if ($new) {
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
        $this->populateProperty();
        return $this->property->write();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'module' => ['taxonomy'],
        ];
    }

    /**
     *
     */
    public function getEntityType()
    {
        return static::ENTITY_TYPE;
    }

    public function setDependencies(ApplicationEvent $event)
    {
        $event->addDependencies($this->getDependencies());
    }

    /**
     * Filled $property.
     */
    protected function populateProperty()
    {
        if (null === $this->property) {
            $this->setProperty(new TaxonomyTermProperty($this));
        }
    }

    /**
     * Mengeset $property dengan TaxonomyTermPropertyInterface().
     */
    protected function setProperty(TaxonomyTermPropertyInterface $property)
    {
        $this->property = $property;
    }
}
