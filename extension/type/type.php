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
     * @param string $base_path
     */
    public function getManifest($base_path)
    {
        return $this->loadXml($base_path,'extension');
    }

    /**
     * @param $base_path
     * @param string $file
     * @return bool|\SimpleXMLElement
     */
    public function loadXml($base_path, $file)
    {
        $file = str_replace('.xml','',$file);
        $manifest_path = FilesystemPath::find($base_path,$file.'.xml');
        if (!$manifest_path) return false;

        return simplexml_load_file($manifest_path, __NAMESPACE__.'\XmlElement');
    }
}