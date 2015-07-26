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
     * Array alias resources
     *
     * @var array
     */
    protected $_alias;

    /**
     * The application message queue.
     *
     * @var    array
     */
    protected $messageQueue = array();

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

        //
        $rc = new \ReflectionClass($this);
        $result = [];
        preg_match_all('/@(\w+)\s+(.*)\r?\n/m', $rc->getDocComment(), $matches);

        $doc_block = [];
        foreach ($matches[1] as $index => $value) {
            $doc_block[$value][] = $matches[2][$index];
        }
        $this->_alias = [];
        foreach ($doc_block['var'] as $var) {
            list($class, $variable) = explode(' ', $var);
            $variable = trim(substr($variable,1));
            $this->_alias[$variable] = $class;
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
     * Enqueue a system message.
     *
     * @param   string  $msg   The message to enqueue.
     * @param   string  $type  The message type. Default is message.
     * @param   array   $attributes  Array attributes
     *
     * @return  void
     */
    public function enqueueMessage($msg, $type = 'message', $attributes = [])
    {
        // Don't add empty messages.
        if (!strlen($msg))
        {
            return;
        }

        // For empty queue, if messages exists in the session, enqueue them first.
        $this->getMessageQueue();

        // Enqueue the message.
        $this->messageQueue[] = array('message' => $msg, 'type' => strtolower($type), 'attributes' => $attributes);
    }

    /**
     * Get the system message queue.
     *
     * @return  array  The system message queue.
     */
    public function getMessageQueue()
    {
        // For empty queue, if messages exists in the session, enqueue them.
        if (!count($this->messageQueue))
        {
            $session = Session::getInstance();
            $sessionQueue = $session->get('application.queue');

            if (count($sessionQueue))
            {
                $this->messageQueue = $sessionQueue;
                $session->set('application.queue', null);
            }
        }

        return $this->messageQueue;
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
        if (!isset($this->$key) && in_array($key, array_keys($this->_alias))) {
            $class = $this->_alias[$key];
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

        return isset($this->$key) ? $this->$key : null ;
    }
}