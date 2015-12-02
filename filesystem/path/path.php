<?php
namespace Cyan\Library;

/**
 * Class FilesystemPath
 * @package Cyan\Library
 */
abstract class FilesystemPath
{
    public static function find($paths, $file)
    {
        if (is_string($paths)) {
            $paths = [$paths];
        }

        foreach ($paths as $path) {
            $filePath = sprintf('%s'.DIRECTORY_SEPARATOR.'%s',$path, $file);
            if (file_exists($filePath)) {
                return $filePath;
            }
        }

        return false;
    }
}