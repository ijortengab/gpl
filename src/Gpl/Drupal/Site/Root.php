<?php

namespace Gpl\Drupal\Site;

/**
 * Class yang berfungsi mencari web root Drupal dan mendefinisikan
 * constant DRUPAL_ROOT.
 * Jika tidak ditemukan, maka keseluruhan proses dihentikan dengan die().
 */
class Root
{
    /**
     * Mencari Drupal berdasarkan perkiraan path file `includes/bootstrap.inc`.
     */
    public static function find()
    {
        try {
            $cwd = getcwd();
            if (file_exists($cwd . '/includes/bootstrap.inc')) {
                define('DRUPAL_ROOT', $cwd);
            }
            else{
                throw new \Exception('Drupal not found.'.PHP_EOL);
            }
        }
        catch (\Exception $e) {
            die($e->getMessage());
        }
    }
}
