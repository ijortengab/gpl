<?php

use Gpl\Application\Application;
use Gpl\Drupal;

Drupal\Site\Root::find();

$application = new Application();
$application->run();
