<?php

namespace Gpl\Site;

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

        // return $this;

    }

    /**
     *
     */
    public function generate()
    {

        // $process = new Process([
            // 'sudo',
            // '-k',
            // 'ls',
        // ]);
        // $process->run(function ($type, $buffer) {
            // if (Process::ERR === $type) {
                // echo 'ERR > '.$buffer;
            // } else {
                // echo 'OUT > '.$buffer;
            // }
        // });

        // Check apakah Drupal sudah diinstall.
        $drupalFinder = new \DrupalFinder\DrupalFinder();
        if ($drupalFinder->locateRoot($this->working_directory)) {
            $drupalRoot = $drupalFinder->getDrupalRoot();
            $composerRoot = $drupalFinder->getComposerRoot();
        }
        else {
            // drupal belum diinstall.
            $this->downloadDrupalCodeBase();
        }
        return;
    }

    /**
     *
     */
    public function setWorkingDirectory($dir)
    {
        // @todo, verifikasi directory.
        $this->working_directory = $dir;
    }

    /**
     *
     */
    protected function downloadDrupalCodeBase()
    {
        // Require.
        if (null === (new ExecutableFinder)->find('composer')) {
            throw new \Exception('Composer required for download Drupal.'.PHP_EOL);
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

}
