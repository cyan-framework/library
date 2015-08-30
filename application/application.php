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
    protected $messageQueue = [];

    /**
     * Start off the number of deferrals at 1. This will be
     * decremented by the Application's own `initialize` method.
     *
     * @var int
     */
    protected $_readinessDeferrals = 1;

    /**
     * @var Text
     */
    public $Text;

    /**
     * @var Cache
     */
    public $Cache;

    /**
     * @var FactoryDatabase
     */
    public $Database;

    /**
     * @var Router
     */
    public $Router;

    /**
     * @var FactoryController
     */
    public $Controller;

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

        $this->Router = Router::getInstance();
        $this->Controller = FactoryController::getInstance();
        $this->Database = FactoryDatabase::getInstance();
        $this->Text = Text::getInstance();
        $this->Cache = Cache::getInstance();

        //setup router
        $this->Router->setContainer('application', $this);

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
     * Get Theme
     *
     * @return string
     */
    public function getTheme()
    {
        return !empty($this->getConfig()['theme']) ? $this->getConfig()['theme'] : 'application' ;
    }

    /**
     * Get Language Default
     *
     * @return string
     */
    public function getLanguage()
    {
        $session = Session::getInstance();
        $app_lang = !empty($this->getConfig()['language']) ? $this->getConfig()['language'] : null ;
        return $session->get('app.lang', $app_lang);
    }

    /**
     * Set a Language
     *
     * @param $lang
     */
    public function setLanguage($lang)
    {
        $session = Session::getInstance();
        $session->set('app.lang', $lang);
    }

    /**
     * Enqueue a system message.
     *
     * @param   string  $msg   The message to enqueue.
     * @param   string  $type  The message type. Default is message.
     * @param   array   $attributes  Array attributes
     *
     * @return  self
     */
    public function enqueueMessage($msg, $type = 'message', $attributes = [])
    {
        // Don't add empty messages.
        if (!strlen($msg))
        {
            return $this;
        }

        // For empty queue, if messages exists in the session, enqueue them first.
        $this->getMessageQueue();

        $this->messageQueue[] = ['message' => addslashes($msg), 'type' => strtolower($type), 'attributes' => $attributes];

        // For empty queue, if messages exists in the session, enqueue them.
        $session = Session::getInstance();
        $session->set('application.queue', $this->messageQueue);

        return $this;
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
            // For empty queue, if messages exists in the session, enqueue them.
            $session = Session::getInstance();
            $sessionQueue = $session->get('application.queue', []);

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
}