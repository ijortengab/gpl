<?php
namespace Gpl\Application;

interface ApplicationInterface
{
    /**
     * Set property $yaml.
     */
    public function setInfo($yaml);

    /**
     * Get Yaml.
     */
    public function getInfo();

    /**
     * Melakukan analisis object sebelum dieksekusi.
     */
    public function analyze();

    /**
     * Eksekusi object.
     */
    public function execute();
}
