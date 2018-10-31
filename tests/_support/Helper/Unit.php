<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Unit extends \Codeception\Module
{
    public static function rrmdir($dir = '') {
        $dir = realpath($dir);
        if ($dir) {
            $files = array_diff(scandir($dir), ['.','..']);
            foreach ($files as $file)
                (is_dir("$dir/$file")) ? self::rrmdir("$dir/$file") : unlink("$dir/$file");
            return rmdir($dir);
        }
        return null;
    }
}
