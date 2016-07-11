<?php
namespace Cyan\Framework;

/**
 * Class ExtensionType
 * @package Cyan\Framework
 * @since 1.0.0
 */
abstract class ExtensionType
{
    use TraitContainer, TraitFilepath;

    /**
     * @param $base_path
     * @return mixed
     */
    abstract function register($base_path);

    /**
     * @param $base_path
     * @return mixed
     */
    abstract function discover($base_path);

    /**
     * @param $base_path
     * @return mixed
     */
    abstract function install($base_path);

    /**
     * @param $base_path
     */
    public function getManifest($base_path)
    {
        $manifest_path = FilesystemPath::find($base_path,'extension.xml');
        if (!$manifest_path) return false;
        
        return simplexml_load_file($manifest_path, __NAMESPACE__.'\XmlElement');
    }
}