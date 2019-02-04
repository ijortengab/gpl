<?php
namespace Gpl\Application;

use Symfony\Component\EventDispatcher\Event;

class ApplicationEvent extends Event
{
    protected $dependencies = [
        'module' => [],
    ];

    /**
     *
     */
    public function addDependencies(Array $info)
    {
        $this->dependencies = array_merge_recursive($this->dependencies, $info);
        return $this;
    }

    /**
     *
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }
}
