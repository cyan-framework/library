<?php
namespace Cyan\Framework;

/**
 * Class Extension
 * @package Cyan\Framework
 * @since 1.0.0
 */
class Extension
{
    use TraitSingleton, TraitContainer, TraitFilepath;

    /**
     * @param $adapter
     * @throws ArchitectureException
     */
    public static function get($type)
    {
        $type = strtolower($type);
        if ($file = FilesystemPath::find(self::addIncludePath(), $type . DIRECTORY_SEPARATOR . $type . '.php')) {
            require_once $file;
            $class_name = __NAMESPACE__.'\\ExtensionType'.ucfirst($type);
            if (!class_exists($class_name)) {
                throw new ExtensionException(sprintf('Extension type "%s" not found!',ucfirst($type)));
            }
            $required_traits = [
                'Cyan\Framework\TraitSingleton'
            ];

            $reflection_class = new ReflectionClass($class_name);
            foreach ($required_traits as $required_trait) {
                if (!in_array($required_trait,$reflection_class->getTraitNames())) {
                    throw new ExtensionException(sprintf('%s class must use %s', $class_name, $required_trait));
                }
            }

            if (!is_callable([$class_name,'register'])) {
                throw new ExtensionException(sprintf('%s class must implement register method', $class_name));
            }

            return $class_name::getInstance();
        }

        throw new ExtensionException(sprintf('Extension "%s" not found!',ucfirst($type)));
    }
}

Extension::addIncludePath(__DIR__ . DIRECTORY_SEPARATOR . 'type');