<?php
namespace Cyan\Library;

/**
 * Class ApplicationWeb
 * @package Cyan\Library
 */
class ApplicationWeb extends Application
{
    /**
     * @var FactoryView
     */
    public $View;

    /**
     * @var Theme
     */
    public $Theme;

    /**
     * Default Application
     */
    public function initialize()
    {
        $filters = Finder::getInstance()->getIdentifier('app:config.filters', []);
        if (!empty($filters)) {
            Filter::getInstance()->mapFilters($filters);
        }

        $this->View = FactoryView::getInstance();
        if (!isset($this->_data['build_theme'])) {
            $this->Theme = Theme::getInstance();
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

        //setup cache
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

        //setup database
        $db_configs = Finder::getInstance()->getIdentifier('app:config.database', []);
        foreach ($db_configs as $db_name => $db_config) {
            $this->Database->create($db_name, $db_config);
        }

        //setup language
        $this->Text->loadLanguage($this->getLanguage());

        if (!isset($this->_data['build_theme'])) {
            $view = new View([
                'path' => $this->Theme->getPath().'/',$this->getTheme()
            ]);
            $view->set('messages', $this->getMessageQueue())->tpl($this->getTheme(),'messages');
            $this->Theme->set('system_messages', (string)$view);
        }

        $this->trigger('BeforeRun', $this);

        $response = $this->Router->run();

        $this->trigger('AfterRun', $this);

        if (!isset($this->_data['build_theme'])) {
            $this->Theme->setContainer('application', $this);
            $this->Theme->set('outlet', (string)$response);

            echo $this->Theme->tpl($this->getTheme());
        } else {
            echo $response;
        }
    }
}