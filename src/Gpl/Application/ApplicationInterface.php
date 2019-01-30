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

    /**
     * Melakukan eksekusi penulisan ke database.
     */
    public function write();

    /**
     * Mendapatkan dependensi dari objek.
     */
    public function getDependencies();
}
