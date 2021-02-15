<?php

use Gpl\Environment;
use Gpl\Generator;
use Gpl\Config;
use Gpl\Generator\Drupal\Drupal;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ExecutableFinder;

require 'vendor/autoload.php';

if (!file_exists("gpl.yml")) {
    die('The gpl.yml file not found.'.PHP_EOL);
}
$cwd = isset($_SERVER['PWD']) && is_dir($_SERVER['PWD']) ? $_SERVER['PWD'] : getcwd();

// Define variable ENVIRONMENT in two locations. Example:
// 1. /etc/bash.bashrc
//    put this line:
//    ```
//    export ENVIRONMENT="localhost"
//    ```
//    this will be used every shell interactive.
// 2. /etc/environment
//    put this line:
//    ```
//    ENVIRONMENT="localhost"
//    ```
//    this environment is known if execute by sudo.
try {
    $config = new Config(Yaml::parseFile($cwd.'/gpl.yml'));
    $environment = new Environment(getenv('ENVIRONMENT'));
    // Set Yaml Configuration, then populate all object instance based on
    // YAML Configuration.
    $environment->setConfig($config)->populateAll();
    $generator = new Generator($environment);
    $generator->execute();
}
catch (Exception $e) {
    die($e->getMessage().PHP_EOL);
}
