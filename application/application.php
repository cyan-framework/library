<?php
namespace Cyan\Library;

/**
 * Class Application
 * @package Cyan\Library
 */
abstract class Application
{
    use TraitsPrototype, TraitsEvent, TraitsContainer;

    /**
     * Application Name
     *
     * @var string
     */
    protected $_name;

    /**
     * List set Data
     *
     * @var \ArrayObject
     */
    protected $_data;

    /**
     * Start off the number of deferrals at 1. This will be
     * decremented by the Application's own `initialize` method.
     *
     * @var int
     */
    protected $_readinessDeferrals = 1;

    /**
     * Application Constructor
     */
    public function __construct()
    {
        $args = func_get_args();

        switch (count($args)) {
            case 2:
                if (!is_string($args[0]) && !is_callable($args[1])) {
                    throw new ApplicationException('Invalid argument orders. Spected (String, Closure) given (%s,%s).',gettype($args[0]),gettype($args[1]));
                }
                $name = $args[0];
                $initialize = $args[1];
                break;
            case 1:
                if (is_string($args[0])) {
                    $name = $args[0];
                } elseif (is_callable($args[0])) {
                    $initialize = $args[0];
                } else {
                    throw new ApplicationException('Invalid argument type! Spected String/Closure, "%s" given.',gettype($args[0]));
                }
                break;
            case 0:
                break;
            default:
                throw new ApplicationException('Invalid arguments. Spected (String, Closure).');
                break;
        }

        //create default name
        if (!isset($name)) {
            throw new ApplicationException('You must send a name');
        }

        $this->_name = $name;
        $this->_registry = new \ArrayObject();
        $this->_data = new \ArrayObject();

        if (isset($initialize) && is_callable($initialize)) {
            $this->__initializer = $initialize->bindTo($this, $this);
            $this->__initializer();
        }

        $this->advanceReadiness();
    }

    /**
     * Read Application Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Define Application Name
     *
     * @param $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Increase readiness state
     */
    public function deferReadiness()
    {
        $this->_readinessDeferrals++;
    }

    /**
     * Decrease readiness state
     */
    public function advanceReadiness()
    {
        if ($this->_readinessDeferrals) {
            $this->_readinessDeferrals--;
        }

        $this->trigger('Ready', $this);
    }

    /**
     * Read Application Config
     *
     * @return Array
     */
    public function getConfig()
    {
        return Finder::getInstance()->getIdentifier('app:config.application', []);
    }

    /**
     * Listen PHP Built in Server
     */
    public function listen()
    {
        if (php_sapi_name() == 'cli-server') {
            $this->run();
        }

        return $this;
    }

    /**
     * Create instance according with docblock class var
     *
     * @param $key
     * @return null
     */
    public function __get($key)
    {
        if (!isset($this->$key)) {
            $rc = new \ReflectionClass($this);
            $result = [];
            preg_match_all('/@(\w+)\s+(.*)\r?\n/m', $rc->getDocComment(), $matches);

            $doc_block = [];
            foreach ($matches[1] as $index => $value) {
                $doc_block[$value][] = $matches[2][$index];
            }

            foreach ($doc_block['var'] as $var) {
                list($class, $variable) = explode(' ',$var);
                $variable = substr($variable,1);
                if ($variable === $key) {
                    $this->$key = $class::getInstance();
                    //custom behaviors
                    switch ($key) {
                        case 'Cache':
                            $defaultCacheConfig = [
                                'cache_path' => Finder::getInstance()->getPath('app:cache').DIRECTORY_SEPARATOR,
                                'cache_time' => 172800  //48 hours cache
                            ];
                            $cacheConfig = Finder::getInstance()->getIdentifier('app:config.application', $defaultCacheConfig);
                            if (!isset($cacheConfig['cache_path'])) {
                                $cacheConfig = array_merge($cacheConfig, $defaultCacheConfig);
                            }

                            $this->Cache->setCachePath($cacheConfig['cache_path']);
                            $this->Cache->setCacheTime($cacheConfig['cache_time']);
                            break;
                        case 'Database':
                            $db_configs = Finder::getInstance()->getIdentifier('app:config.database', []);
                            foreach ($db_configs as $db_name => $db_config) {
                                $this->Database->create($db_name, $db_config);
                            }
                            break;
                        case 'Router':
                            $this->Router->setContainer('application', $this);
                            break;
                        case 'Text':
                            $language = !empty($this->getConfig()['language']) ? $this->getConfig()['language'] : '' ;
                            if (!empty($language)) {
                                $this->Text->loadLanguage($language);
                            }
                            break;
                    }
                }
            }
        }

        return isset($this->$key) ? $this->$key : null ;
    }
}