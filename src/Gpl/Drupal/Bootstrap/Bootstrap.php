<?php
namespace Gpl\Drupal\Bootstrap;

/**
 * Loading Drupal Bootstrap.
 */
class Bootstrap
{
    /**
     * Eksekusi fungsi drupal_bootstrap().
     */
    public static function load()
    {
        // Define default settings.
        $cmd = 'index.php';
        $_SERVER['HTTP_HOST']       = 'default';
        $_SERVER['PHP_SELF']        = '/index.php';
        $_SERVER['REMOTE_ADDR']     = '127.0.0.1';
        $_SERVER['SERVER_SOFTWARE'] = NULL;
        $_SERVER['REQUEST_METHOD']  = 'GET';
        $_SERVER['QUERY_STRING']    = '';
        $_SERVER['PHP_SELF']        = $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['HTTP_USER_AGENT'] = 'console';
        include DRUPAL_ROOT . '/includes/bootstrap.inc';
        include DRUPAL_ROOT . '/includes/utility.inc';
        drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
    }
}
