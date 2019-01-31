<?php
namespace Gpl\Drupal\Entity\User;

use Gpl\Application\Application;
use Gpl\Application\ApplicationInterface;
use Gpl\Drupal\Entity\EntityInterface;
use Gpl\Drupal\Entity\AbstractEntity;

class User extends AbstractEntity implements ApplicationInterface, EntityInterface
{
    const ENTITY_TYPE = 'user';

    /**
     * Hasil analyze().
     */
    protected $analyze;

    /**
     * Instance dari NodePropertyInterface().
     */
    protected $property;


    /**
     * Mendapatkan dan autocreate instance self dengan kemudian menyimpannya
     * dalam property $bundles. Identifiernya adalah entity bundle name.
     */
    public static function getBundle($machine_name)
    {
        // User hanya punya satu bundle, yakni: user.
        if ($machine_name !== 'user') {
            // todo lempar ke exception.
            // Sementara: paksa ke bundle user.
            $machine_name = 'user';
        }
        return parent::getBundle($machine_name);
    }

    /**
     * Construct. Fleksibel baik bundle belum didefinisikan, atau bundle belum
     * ada di database.
     */
    public function __construct($bundle_name)
    {
        $this->bundle_name = $bundle_name;
        $this->is_dependencies_fulfilled = true;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function analyze()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function write()
    {
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
        return static::getEntityType();
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
