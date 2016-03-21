<?php
namespace Cyan\Framework;

/**
 * Class Cache
 * @package Cyan\Framework
 * @since 1.0.0
 *
 * @method Cache getInstance
 */
class Cache
{
    use TraitSingleton;

    /**
     * Path to cache folder (with trailing /)
     *
     * @var string
     * @since 1.0.0
     */
    private $cache_path = '';

    /**
     * Length of time to cache a file in seconds
     *
     * @var int
     * @since 1.0.0
     */
    private $cache_time = 3600;

    /**
     * setup cache
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->cache_path = null;
    }

    /**
     * Return cache path
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getPath()
    {
        return $this->cache_path;
    }

    /**
     * Write a cache file
     *
     * @param string $file_name
     * @param string $data
     * @param string $path
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function write($file_name, $data, $path = '')
    {
        return file_put_contents($this->filePath($file_name, $path), $data);
    }

    /**
     * read a cache based on label path
     *
     * @param $file_name
     * @param string $path
     * @param int $cache_time
     *
     * @return bool|string
     *
     * @since 1.0.0
     */
    public function read($file_name, $path = '', $cache_time = 0)
    {
        if($this->exists($file_name, $path, $cache_time)) {
            return file_get_contents($this->filePath($file_name, $path));
        }

        return false;
    }

    /**
     * Check if file exists and if its valid
     *
     * @param $file_name
     * @param string $path
     * @param int $cache_time
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function exists($file_name, $path = '', $cache_time = 0)
    {
        if (!$cache_time) {
            $cache_time = $this->cache_time;
        }

        if ($this->fileExists($file_name, $path) && ($this->getFiletime($file_name, $path) + $cache_time >= time())) {
            return true;
        }
        // remove file
        if ($this->fileExists($file_name, $path)) {
            unlink($this->filePath($file_name, $path));
        }

        return false;
    }

    /**
     * Return left filetime
     *
     * @param $file_name
     * @param $path
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getLeftFiletime($file_name, $path = '')
    {
        return $this->getFiletime($file_name, $path) - time();
    }

    /**
     * return filetime
     *
     * @param $file_name
     * @param $path
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getFiletime($file_name, $path = '')
    {
        return filemtime($this->filePath($file_name, $path));
    }

    /**
     * Set cache path
     *
     * @param $cache_path
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setCachePath($cache_path)
    {
        return $this->cache_path = $cache_path;

        return $this;
    }

    /**
     * set cache time
     *
     * @param $cache_time
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setCacheTime($cache_time)
    {
        $this->cache_time = $cache_time;

        return $this;
    }

    /**
     * return cache time
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getCacheTime()
    {
        return $this->cache_time;
    }

    /**
     * Check if file exists
     *
     * @param $file_name
     * @param $path
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function fileExists($file_name, $path = '')
    {
        return (file_exists($this->filePath($file_name, $path))) ? true : false ;
    }

    /**
     * path from file
     *
     * @param $file_name
     * @param $path
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function filePath($file_name, $path = '')
    {
        if (!file_exists($this->cache_path)) {
            throw new CacheException(sprintf('Cache path not found: %s',$this->cache_path));
        }
        $filename = $this->cache_path . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $this->createFilename($file_name) .'.cache';

        return str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR,$filename);
    }

    /**
     * Helper function to validate filenames
     *
     * @param $file_name
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function createFilename($file_name)
    {
        return preg_replace('/[^\w+]/i','', strtolower($file_name));
    }
}
