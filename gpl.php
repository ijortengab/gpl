<?php

use Gpl\Generator\Drupal\Drupal;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ExecutableFinder;

$cwd = isset($_SERVER['PWD']) && is_dir($_SERVER['PWD']) ? $_SERVER['PWD'] : getcwd();

require 'vendor/autoload.php';

if (!file_exists("gpl.yml")) {
    die('The gpl.yml file not found.'.PHP_EOL);
}

if (null === (new ExecutableFinder)->find('composer')) {
    die('Composer required.'.PHP_EOL);
}

$array = Yaml::parseFile($cwd.'/gpl.yml');

foreach ($array as $each) {
    if (isset($each['environment']['generator'])) {
        $generator = $each['environment']['generator'];
        switch ($generator) {
            case 'drupal':
                Drupal::generate($each['environment']);
                break;

            case '':
                // Do something.
                break;

            default:
                // Do something.
                break;
        }

    }
}
