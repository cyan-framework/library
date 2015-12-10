<?php
namespace Cyan\Library;


/**
 * Class View
 * @package Cyan\Library
 */
class View
{
    use TraitsEvent, TraitsContainer;

    /**
     * Base path to views
     *
     * @var String
     */
    protected $_path;

    /**
     * Layout Buffer
     *
     * @var string
     */
    protected $_content;

    /**
     * Data Mapper
     *
     * @var array
     */
    protected $data = [];

    /**
     * @var Finder
     */
    public $finder;

    /**
     * Static global Data
     *
     * @var array
     */
    protected static $share = [];

    /**
     * View Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $Cyan = \Cyan::initialize();
        $this->finder = $Cyan->Finder;
        $App = $Cyan->Application->current;

        $default_path = $Cyan->getRootPath() . DIRECTORY_SEPARATOR . 'view' ;
        if ($this->finder->hasResource('app') && !isset($config['path'])) {
            $config['path'] = $this->finder->getPath('app:view');
        }
        $this->_path = isset($config['path']) ? $config['path'] : $default_path ;

        if (isset($config['tpl'])) {
            $this->tpl($config['tpl']);
        }

        if ($App instanceof Application) {
            $this->setContainer('application', $App);
            $this->set('app_name', $App->getName());
            $this->set('outlet', '');

            $appConfig = $App->getConfig();

            $base_url = $App->Router->getBase(true);
            $assets_url = isset($appConfig['assets_url']) ? $appConfig['assets_url'] : null;
            if (is_null($assets_url) && isset($config['assets_url'])) {
                $assets_url = $config['assets_url'];
            }
            if (is_null($assets_url)) {
                $assets_url = $base_url;
            }

            $this->set('base_url', $App->Router->getBase());
            $this->set('assets_url', $assets_url);
            $this->set('title', isset($appConfig['title']) ? $appConfig['title'] : $App->getName() );
            $this->set('app_name', $App->getName());
        }

        $this->trigger('Initialize', $this);
    }

    /**
     * Set Path
     *
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->_path = $path;

        return $this;
    }

    /**
     * @return String
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Set Data
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Get Data
     *
     * @param $key
     * @return null
     */
    public function get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null ;
    }

    /**
     * Define array of data
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
        return $this;
    }

    /**
     * Share Data Between all views
     *
     * @param $key
     * @param $value
     */
    public static function share($key, $value)
    {
        self::$share[$key] = $value;
    }

    /**
     * @param $key
     * @return mixed
     */
    public static function getShared($key)
    {
        return isset(self::$share[$key]) ? self::$share[$key] : null ;
    }

    /**
     * @param array $data
     */
    public static function shareData(array $data)
    {
        foreach ($data as $key => $value) {
            self::share($key, $value);
        }
    }

    /**
     * Render Layout
     *
     * @param $folder
     * @param null $layout
     * @param null $path
     * @return mixed
     * @throws RuntimeException
     */
    public function tpl($folder, $layout = null, $path = null)
    {
        if (is_null($folder)) {
            throw new ViewException('Folder cant be null');
        }

        $layout = is_null($layout) ? 'index' : $layout ;
        $base_path = !is_null($path) ? $path : $this->_path ;
        $view_path = sprintf('%s/%s', $base_path, $folder);
        $layout_path = sprintf('%s/%s.php', $view_path, $layout);

        if (empty($folder) || !is_dir($view_path)) {
            throw new ViewException(sprintf('Layout folder path "%s" not found',$view_path));
        }

        if (!file_exists($layout_path)) {
            throw new ViewException(sprintf('Layout not found "%s"', $layout_path));
        }

        $this->layout_path = $layout_path;

        return $this;
    }

    /**
     * Render Layout
     *
     * @param $folder
     * @param null $layout
     * @param null $path
     * @return mixed
     * @throws RuntimeException
     */
    public function display($folder, $layout = null, $path = null)
    {
        return $this->tpl($folder, $layout, $path);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->_content = $content;
        return $this;
    }

    /**
     * Render a layout
     */
    public function render()
    {
        $Cyan = \Cyan::initialize();

        ob_start();
        include $this->layout_path;
        $this->_content = ob_get_clean();

        //import application plugins
        FactoryPlugin::getInstance()->assign('view', $this);
        $this->trigger('Render', $this);

        return $this->_content;
    }

    /**
     * Return link
     *
     * @param $uri
     * @param array $config
     */
    public function linkTo($name, array $config = [])
    {
        $App = $this->getContainer('application');

        return is_null($App) ? $text : $App->Router->generate($name, $config);
    }

    /**
     * Translate a text
     *
     * @param $text
     * @return mixed
     */
    public function translate($text)
    {
        $App = $this->getContainer('application');

        return is_null($App) ? $text : $App->Text->translate($text);
    }

    /**
     * Translate a text using sprintf
     *
     * @param $text
     * @return mixed
     */
    public function sprintf()
    {
        $args = func_get_args();
        $App = $this->getContainer('application');

        return is_null($App) ? call_user_func_array('sprintf', $args) : call_user_func_array([$App->Text,'srptinf'], $args);
    }

    /**
     * Output a view
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (RouterException $e) {
            die($e->getMessage());
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }
}