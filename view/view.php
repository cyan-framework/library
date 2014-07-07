<?php
namespace Cyan\Library;


/**
 * Class View
 * @package Cyan\Library
 */
class View
{
    use TraitsEvent;

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
    protected $data = array();

    /**
     * @var Finder
     */
    public $finder;

    /**
     * Static global Data
     *
     * @var array
     */
    private static $share = array();

    /**
     * View Constructor
     *
     * @param array $config
     */
    final public function __construct(array $config = array())
    {
        $this->finder = Finder::getInstance();

        $default_path = \Cyan::initialize()->getRootPath() . DIRECTORY_SEPARATOR . 'view' ;
        if ($this->finder->hasResource('app')) {
            $config['path'] = $this->finder->getPath('app:view');
        }
        if (!empty(\Cyan::initialize()->Application->current)) {
            $this->set('app_name', \Cyan::initialize()->Application->current->getName());
            $this->set('outlet', '');
        }
        $this->_path = isset($config['path']) ? $config['path'] : $default_path ;

        if (isset($config['tpl'])) {
            $this->tpl($config['tpl']);
        }

        $app = \Cyan::initialize()->Application->current;

        if ($app instanceof Application) {
            $app_config = $app->getConfig();

            if (substr($app->Router->base,-4) === '.php') {
                $base_url = str_replace(basename($app->Router->base),'',$app->Router->base);
            } else {
                $base_url = $app->Router->base;
            }

            $this->set('base_url', $app->Router->base);
            $this->set('assets_url', rtrim($base_url));
            $this->set('title', isset($app_config['title']) ? $app_config['title'] : $app->getName() );
            $this->set('app_name', $app->getName());
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
    final public function tpl($folder, $layout = null, $path = null)
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
    final public function render()
    {
        ob_start();
        include $this->layout_path;
        $this->_content = ob_get_clean();

        $this->trigger('Render', $this);

        return $this->_content;
    }

    /**
     * Return link
     *
     * @param $uri
     * @param array $config
     */
    public function link_to($uri, array $config = array())
    {
        return \Cyan::initialize()->Application->current->Router->link_to($uri, $config);
    }

    /**
     * Output a view
     */
    public function __toString()
    {
        return $this->render();
    }
}