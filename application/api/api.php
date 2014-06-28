<?php
namespace Cyan\Library;

class ApplicationApi
{
    /**
     * Traits
     */
    use TraitsApplication;

    /**
     * Create Application
     */
    public function __construct($name)
    {
        $that = $this;
        $this->_name = $name;
        $this->router = new RouterApi;
        $this->finder = Finder::getInstance();
        $this->connection = Connection::getMultiton($this->getName(), $this->finder->getIdentifier('app:config.database', array()));

        $this->trigger('Initialize', $this);
    }

    /**
     * Run Api
     */
    public function run()
    {
        $output = $this->router->run();

        $supress_response_codes = (isset($_GET['supress_response_codes'])) ? true : false ;

        if ($supress_response_codes) {
            $code = 200;
        } else {
            $code = $output['status'];
            unset($output['status']);
        }

        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        $text = (!isset($output['message'])) ? 'OK' : $output['message'] ;
        header("Access-Control-Allow-Origin: *");
        header($protocol . ' ' . $code . ' ' . $text);

        $callback = isset($_GET['callback']) ? Filter::getInstance()->filter('cyan_callback_func', $_GET['callback']) : '' ;

        if (!empty($callback)) {
            $template = $callback.'(%s)';
        } else {
            $template = '%s';
        }

        return sprintf($template,json_encode($output));
    }
}