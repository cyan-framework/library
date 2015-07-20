<?php
namespace Cyan\Library;

/**
 * Class ApplicationApi
 * @package Cyan\Library
 *
 * @var \Cyan\Library\Text $Text
 * @var \Cyan\Library\Cache $Cache
 * @var \Cyan\Library\FactoryDatabase $Database
 * @var \Cyan\Library\Router $Router
 * @var \Cyan\Library\FactoryController $Controller
 */
class ApplicationApi extends Application implements ApplicationInterface
{
    /**
     * Debug response time
     *
     * @var bool
     */
    private $_debug = false;

    /**
     * Enable debug
     *
     * @return $this
     */
    public function enableDebug()
    {
        $this->_debug = true;

        return $this;
    }

    /**
     * Disable debug
     *
     * @return $this
     */
    public function disableDebug()
    {
        $this->_debug = false;

        return $this;
    }

    /**
     * Default Application
     */
    public function initialize()
    {
        $filters = Finder::getInstance()->getIdentifier('app:config.filters', []);
        if (!empty($filters)) {
            Filter::getInstance()->mapFilters($filters);
        }

        //import application plugins
        FactoryPlugin::getInstance()->assign('api', $this);

        $this->trigger('Initialize', $this);
    }

    /**
     * Run Api
     */
    public function run()
    {
        $output = $this->Router->run();

        $supress_response_codes = (isset($_GET['supress_response_codes'])) ? true : false ;

        if ($supress_response_codes) {
            $code = 200;
        } else {
            $code = isset($output['status']) ? $output['status'] : 200 ;
            unset($output['status']);
        }

        $headers = isset($output['header']) ? $output['header'] : '';
        if (isset($output['header'])) {
            unset($output['header']);
        }

        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        $text = (!isset($output['header_message'])) ? 'OK' : $output['header_message'] ;
        header($protocol . ' ' . $code . ' ' . $text);

        $callback = isset($_GET['callback']) ? Filter::getInstance()->filter('cyan_callback_func', $_GET['callback']) : '' ;

        if (!empty($callback)) {
            $template = $callback.'(%s)';
        } else {
            $template = '%s';
        }

        if ($this->_debug) {
            $execution_time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];

            $memory_usage = memory_get_usage(true);
            $unit=array('b','kb','mb','gb','tb','pb');
            $memory = @round($memory_usage/pow(1024,($i=floor(log($memory_usage,1024)))),2).' '.$unit[$i];

            $output['_debug'] = [
                'memory' => $memory,
                'execution_time' => number_format($execution_time) . ' seconds'
            ];
        }
        $json_string = json_encode($output);

        if ($json_string === false) {
            throw new ApplicationException('JSON encode has failed!');
        }

        if (is_string($headers)) {
            header($headers);
        } else if (is_array($headers)) {
            foreach ($headers as $header) {
                header($header);
            }
        }

        echo sprintf($template,$json_string);
    }

    /**
     * Return Error Array from app:config.errors
     *
     * @param $code
     * @return array
     */
    public function error($code, array $message_arguments = [])
    {
        $errors = Finder::getInstance()->getIdentifier('app:config.errors', []);
        //assign error code to response
        if (isset($errors[$code])) {
            $errors[$code]['code'] = $code;
        }
        $error = isset($errors[$code]) ? ['error' => $errors[$code]] : [] ;

        if (isset($error['error']['message'])) {
            $error['error']['message'] = !empty($message_arguments) ? call_user_func_array($this->Text->sprintf,array_merge([$error['error']['message']], $message_arguments)) : $this->Text->translate($error['error']['message']);
        }

        return $error;
    }


}