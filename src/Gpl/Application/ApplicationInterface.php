<?php
namespace Gpl\Application;

interface ApplicationInterface
{
    public function setYaml($yaml);

    public function analyze();

    public function execute();

    public function write();
}
