<?php

namespace Gpl\Generator;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ExecutableFinder;

class Drupal
{
    protected $working_directory;

    /**
     *
     */
    public function execute()
    {
        if (null === (new ExecutableFinder)->find('composer')) {
            throw new \Exception('Composer required.'.PHP_EOL);
        }
        $process = new Process([
            'composer',
            '--working-dir='.$this->working_directory,
            'create-project',
            'drupal/recommended-project',
            '.',
        ]);
        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo 'ERR > '.$buffer;
            } else {
                echo 'OUT > '.$buffer;
            }
        });

    }

    /**
     *
     */
    public function setWorkingDirectory($dir)
    {
        // @todo, verifikasi directory.
        $this->working_directory = $dir;
    }

}
