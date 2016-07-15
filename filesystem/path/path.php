<?php
namespace Cyan\Framework;

/**
 * Class FilesystemPath
 * @package Cyan\Framework
 * @since 1.0.0
 */
abstract class FilesystemPath
{
    /**
     * Find in array of paths a specific file, string path or false if failure
     *
     * @param string|array $paths
     * @param string $file_name
     *
     * @return bool|string
     *
     * @since 1.0.0
     */
    public static function find($paths, $file_name)
    {
        if (is_string($paths)) {
            $paths = [$paths];
        }

        foreach ($paths as $path) {
            $file_path = $path . DIRECTORY_SEPARATOR . $file_name;
            if (file_exists($file_path)) {
                return $file_path;
            }
        }

        return false;
    }

    /**
     * @param $path
     * @param int $mode
     * @return bool
     */
    public static function create($path, $mode = 755)
    {
        return is_dir($path) || mkdir($path, $mode, true);
    }
}