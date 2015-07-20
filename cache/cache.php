<?php
namespace Cyan\Library;

/**
 * Class Cache
 * @package Cyan\Library
 */
class Cache
{
    use TraitsSingleton;

    /**
     * Path to cache folder (with trailing /)
     *
     * @var string
     */
    private $cache_path = 'cache/';

    /**
     * Length of time to cache a file in seconds
     *
     * @var int
     */
    private $cache_time = 3600;

    /**
     * setup cache
     *
     * @param $path
     * @param null $time
     */
    public function __construct($path, $time = null)
    {
        $time = intval($time);

        $this->cache_path = $path;
        $this->cache_time = $time ? $time : 3600 ;
    }

    /**
     * Return cache path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->cache_path;
    }

    /**
     * This is just a functionality wrapper function
     *
     * @param $label
     * @param $url
     * @return mixed
     */
    public function getData($label, $url)
    {
        if($data = $this->read($label)) {
            return $data;
        } else {
            $data = $this->request($url);
            $this->write($label, $data);
            return $data;
        }
    }

    /**
     * Request a url
     *
     * @param $url
     */
    private function request($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    /**
     * @param $label
     * @param $data
     */
    public function write($label, $data, $path)
    {
        file_put_contents($this->cache_path . $path . $this->createFilename($label) .'.cache', $data);
    }

    /**
     * @param $label
     * @return bool|string
     */
    public function read($label, $path = '')
    {
        if($this->exists($label, $path)){
            $filename = $this->cache_path . $path . $this->createFilename($label) .'.cache';
            return file_get_contents($filename);
        }

        return false;
    }

    /**
     * Check if file exists and if its valid
     *
     * @param $label
     * @return bool
     */
    public function exists($label, $path = '')
    {
        $filename = $this->cache_path . $path . $this->createFilename($label) .'.cache';

        if(file_exists($filename) && (filemtime($filename) + $this->cache_time >= time())) return true;

        return false;
    }

    /**
     * return filetime
     *
     * @param $label
     * @return int
     */
    public function getFiletime($label, $path = '')
    {
        $filename = $this->cache_path . $path  . $this->createFilename($label) .'.cache';

        return filemtime($filename);
    }

    /**
     * Set cache path
     *
     * @param $cache_path
     * @return $this
     */
    public function setCachePath($cache_path)
    {
        return $this->cache_path = $cache_path;

        return $this;
    }

    /**
     * set cache time
     */
    public function setCacheTime($cache_time)
    {
        $this->cache_time = $cache_time;
    }

    /**
     * @return int
     */
    public function getCacheTime()
    {
        return $this->cache_time;
    }

    /**
     * Check if file exists
     *
     * @param $label
     * @return bool
     */
    public function fileExists($label, $path = '')
    {
        $filename = $this->cache_path . $path  . $this->createFilename($label) .'.cache';

        return (file_exists($filename)) ? true : false ;
    }

    /**
     * Helper function to validate filenames
     *
     * @param $filename
     * @return mixed
     */
    public function createFilename($filename)
    {
        return preg_replace('/[^0-9a-z\.\_\-]/i','', strtolower($filename));
    }
}