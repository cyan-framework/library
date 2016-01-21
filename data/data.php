<?php
namespace Cyan\Library;

/**
 * Class Data
 * @package Cyan\Library
 * @since 1.0.0
 *
 * @method Data getInstance
 */
class Data
{
    use TraitSingleton;

    /**
     * Array of collections
     *
     * @var array
     * @since 1.0.0
     */
    private $collections = [];

    /**
     * List of collections
     *
     * @return array
     *
     * @since 1.0.0
     */
    public function listCollections()
    {
       return array_keys($this->collections);
    }

    /**
     * Create a collection
     *
     * @param $name
     * @param array $options
     *
     * @return DataCollection
     *
     * @since 1.0.0
     */
    public function collection($name, array $options = [])
    {
        if (!isset($this->collections[$name]))
            $this->collections[$name] = new DataCollection($name, $options);

        return $this->collections[$name];
    }

    /**
     * Remove a collection
     *
     * @param $name
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function deleteCollection($name)
    {
        if (!isset($this->collections[$name]))
            throw new \CyanException(sprintf('DataCollection "%s" not found!',$name));

        unset($this->collections[$name]);

        return $this;
    }
}