<?php
namespace Cyan\Library;

/**
 * Class ApplicationWeb
 * @package Cyan\Library
 *
 * @var \Cyan\Library\Text $Text
 * @var \Cyan\Library\Cache $Cache
 * @var \Cyan\Library\FactoryDatabase $Database
 * @var \Cyan\Library\Router $Router
 * @var \Cyan\Library\FactoryController $Controller
 * @var \Cyan\Library\FactoryView $View
 * @var \Cyan\Library\Theme
 */
class ApplicationWeb extends Application implements ApplicationInterface
{
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
        FactoryPlugin::getInstance()->assign('application', $this);

        $this->trigger('Initialize', $this);
    }

    /**
     * Run a application
     */
    public function run()
    {
        if ($this->_readinessDeferrals) {
            throw new ApplicationException(sprintf('%s Application its not ready to run!',$this->_name));
        }

        if ($this->Router === false) {
            throw new ApplicationException(sprintf('%s Application Router its not defined!',$this->_name));
        }

        if ($this->Router->countRoutes() == 0) {
            throw new ApplicationException(sprintf('%s Application Router not have any route.',$this->_name));
        }

        $this->trigger('BeforeRun', $this);

        $response = $this->Router->run();

        $this->trigger('AfterRun', $this);

        $this->Theme->set('outlet', (string)$response);

        echo $this->Theme;
    }
}