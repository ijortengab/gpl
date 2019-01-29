<?php
namespace Gpl\Application;

/**
 * Class yang menyediakan fungsi static yang bisa digunakan untuk berbagai
 * keperluan.
 */
class Utility
{
    /**
     * Memberikan Label berdasarkan machine_name.
     * Contoh: my_blog menjadi My Blog.
     */
    public static function createLabel($machine_name)
    {
        return preg_replace_callback("/(_)(.)/", function ($matches) {
            return ' ' . strtoupper($matches[2]);
        }, ucfirst($machine_name));
    }
}
