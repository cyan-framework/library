<?php
namespace Cyan\Framework;

/**
 * Class ApplicationWeb
 * @package Cyan\Framework
 * @since 1.0.0
 *
 * @property \Cyan\Framework\Database $Database
 * @property \Cyan\Framework\Text $Text
 * @property \Cyan\Framework\Router $Router
 * @property \Cyan\Framework\Session $Session
 * @property \Cyan\Framework\Theme $Theme
 */
class ApplicationWeb extends ApplicationBase
{
    /**
     * Output string
     *
     * @var string
     * @since 1.0.0
     */
    private $output;

    /**
     * The application message queue.
     *
     * @var    array
     * @since 1.0.0
     */
    protected $messageQueue = [];

    /**
     *
     */
    public function initialize()
    {
        $this->setContainer('factory_view', new FactoryView());

        parent::initialize();
    }

    /**
     * Get Theme
     *
     * @return string
     */
    public function getTheme()
    {
        $config = $this->getConfig();
        return (isset($config['theme']) && !empty($config['theme'])) ? $config['theme'] : 'application' ;
    }

    /**
     * Get Language Default
     *
     * @return string
     */
    public function getLanguage()
    {
        $config = $this->getConfig();
        $app_lang = (isset($config['language']) && !empty($config['language'])) ? $config['language'] : null ;
        return $this->Session->get('app.lang', $app_lang);
    }
    /**
     * Set a Language
     *
     * @param $lang
     */
    public function setLanguage($lang)
    {
        $this->Session->set('app.lang', $lang);
        return $this;
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
        $this->Session->set('application.queue', $this->messageQueue);

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
            $sessionQueue = $this->Session->get('application.queue', []);

            if (count($sessionQueue))
            {
                $this->messageQueue = $sessionQueue;
                $this->Session->set('application.queue', null);
            }
        }

        return $this->messageQueue;
    }

    /**
     * Set Application output
     *
     * @param $output
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setOutput($output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * get application output
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @since 1.0.0
     */
    public function execute()
    {
        if ($this->Router->countRoutes() == 0) {
            throw new ApplicationException(sprintf('%s Application Router not have any route.',$this->name));
        }

        $this->trigger('BeforeExecute', $this);

        $route_info = $this->Router->dispatchFromRequest();

        switch ($route_info[0]) {
            case \Cyan\Framework\Router::ERROR:
                $output = $this->Router->getErrorsAsMessage();
                break;
            case \Cyan\Framework\Router::NOT_FOUND:
                $output = '404';
                break;
            case \Cyan\Framework\Router::METHOD_NOT_ALLOWED:
                $allowed_methods = $route_info[1];
                $output = sprintf('Allowed methods: %s', implode(',', $allowed_methods));
                break;
            case \Cyan\Framework\Router::FOUND:
                $handler = $route_info[1];
                $vars = $route_info[2];
                $route = $route_info[3];

                if (is_callable($handler)) {
                    $return = call_user_func_array($handler, $vars);
                    if (is_string($return)) {
                        echo $return;
                    } elseif (($return instanceof \Cyan\Framework\ApplicationWeb)) {
                        $return->initialize();
                        $output = $return->execute();
                    } else {
                        throw new ApplicationException(sprintf('Response should be String|Cyan\Framework\ApplicationWeb instead of %s', gettype($return)));
                    }
                } elseif (is_array($handler)) {

                    if (!isset($handler['class_name']) && isset($handler['method'])) {
                        throw new ApplicationException('Invalid handle for uri');
                    }

                    $reflection_class = new ReflectionClass($handler['class_name']);
                    if (!in_array('Cyan\Framework\TraitSingleton',$reflection_class->getTraitNames())) {
                        $instance = $handler['class_name']::getInstance();
                    } else {
                        $instance = $reflection_class->newInstance();
                    }

                    if (in_array('Cyan\Framework\TraitContainer',$reflection_class->getTraitNames())) {
                        if (!$instance->hasContainer('application')) {
                            $instance->setContainer('application', $this);
                        }
                        if (!$instance->hasContainer('factory_plugin')) {
                            $instance->setContainer('factory_plugin', $this->getContainer('factory_plugin'));
                        }
                    }
                    $instance->initialize();

                    $method = $handler['method'];
                    $vars = array_values($vars);

                    switch (count($vars)) {
                        case 0:
                            $output = $instance->$method();
                            break;
                        case 1:
                            $output = $instance->$method($vars[0]);
                            break;
                        case 2:
                            $output = $instance->$method($vars[0], $vars[1]);
                            break;
                        case 3:
                            $output = $instance->$method($vars[0], $vars[1], $vars[2]);
                            break;
                        default:
                            $output = call_user_func_array([$instance, $method], $vars);
                            break;
                    }
                } else {
                    throw new ApplicationException(sprintf('Unknow handle dispatch: %s',gettype($handler)));
                }
                break;
        }

        $this->setOutput($output);
        $this->trigger('AfterExecute', $this, $route_info);

        return $this->getOutput();
    }
}