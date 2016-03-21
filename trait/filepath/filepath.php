<?php
namespace Cyan\Framework;

trait TraitFilepath
{
    protected static $file_paths = [];

    /**
     * @param null $path
     * @return array
     */
    public static function addIncludePath($path = null)
    {
        if (!empty($path) && file_exists($path) && is_dir($path)) {
            array_unshift(self::$file_paths,$path);
        }

        self::$file_paths = array_unique(self::$file_paths);

        return self::$file_paths;
    }

    /**
     * @param null $path
     * @return array
     */
    public function addPath($path = null)
    {
        return self::addIncludePath($path);
    }

    /**
     * reset path
     */
    public function resetPath()
    {
        self::$file_paths = [];
    }
}