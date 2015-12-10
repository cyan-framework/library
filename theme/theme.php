<?php
namespace Cyan\Library;

/**
 * Class Theme
 * @package Cyan\Library
 */
class Theme extends View
{
    use TraitsSingleton;

    /**
     * Theme Folder
     *
     * @var string
     */
    protected $folder;

    /**
     * Static global Data
     *
     * @var array
     */
    protected static $share = [
        'header' => [],
        'footer' => []
    ];

    /**
     * View Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $Cyan = \Cyan::initialize();
        $finder = $Cyan->Finder;
        $app = $Cyan->Application->current;
        $appConfig = $app->getConfig();

        $default_path = $Cyan->getRootPath() . DIRECTORY_SEPARATOR . 'theme' ;
        if ($finder->hasResource('app') && !isset($config['path'])) {
            $iniSetup = parse_ini_file($finder->getPath('app:application','.ini'), true);
            $config['path'] = $finder->getPath('app:'.$iniSetup['folder']['theme']);
        }
        $this->_path = isset($config['path']) ? $config['path'] : $default_path ;
        if (isset($appConfig['theme'])) {
            $this->tpl($appConfig['theme']);
        } else {
            $default_app_template = $default_path.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'application.php';
            $app_template = $this->_path.DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'application.php';
            if (file_exists($default_app_template)) {
                $this->tpl('application','application');
            } elseif (file_exists($app_template)) {
                $this->tpl('application','application');
            }
        }

        if ($app instanceof Application) {
            $base_url = $app->Router->getBase(true);
            $assets_url = isset($appConfig['assets_url']) ? $appConfig['assets_url'] : null;
            if (is_null($assets_url) && isset($config['assets_url'])) {
                $assets_url = $config['assets_url'];
            }
            if (is_null($assets_url)) {
                $assets_url = rtrim($base_url);
            }

            $this->set('base_url', isset($appConfig['base_url']) ? $appConfig['base_url'] : isset($config['base_url']) ? $config['base_url'] : $app->Router->getBase());
            $this->set('assets_url', $assets_url);
            $this->set('title', isset($appConfig['title']) ? $appConfig['title'] : $app->getName() );
            $this->set('app_name', isset($appConfig['app_name']) ? $appConfig['app_name'] : $app->getName() );
        }

        $this->trigger('Initialize', $this);
    }

    /**
     * Return folder
     *
     * @return string
     */
    public function getFolder()
    {
        return $this->folder;
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
            throw new ThemeException('Folder cant be null');
        }

        if (!isset($this->folder)) {
            $this->folder = $folder;
        }

        $layout = is_null($layout) ? 'index' : $layout ;
        $base_path = !is_null($path) ? $path : $this->_path ;
        $view_path = sprintf('%s'.DIRECTORY_SEPARATOR.'%s', $base_path, $folder);
        $layout_path = sprintf('%s'.DIRECTORY_SEPARATOR.'%s.php', $view_path, $layout);

        if (empty($folder) || !is_dir($view_path)) {
            throw new ThemeException(sprintf('Layout folder path "%s" not found',$view_path));
        }

        if (!file_exists($layout_path)) {
            throw new ThemeException(sprintf('Layout not found "%s"', $layout_path));
        }

        $this->layout_path = $layout_path;

        return $this;
    }

    /**
     * Render a layout
     */
    final public function render()
    {
        $Cyan = \Cyan::initialize();

        if (!empty($this->layout_path) && is_readable($this->layout_path) & file_exists($this->layout_path)) {
            ob_start();
            include $this->layout_path;
            $this->_content = ob_get_clean();
        } else {
            if (empty($this->_content)) {
                $this->_content = (string) $this->data['outlet'];
            }
        }

        $this->trigger('Render', $this);

        return $this->_content;
    }

    /**
     * Add custom string to head position
     *
     * @param $content
     */
    public function addHeader($content)
    {
        self::$share['header'][] = $content;
    }

    /**
     * get Header
     */
    public function getHeader()
    {
        return !empty(self::$share['header']) ? chr(13).chr(9).implode(chr(13).chr(9),self::$share['header']) : null ;
    }

    /**
     * Add custom string to footer position
     *
     * @param $content
     */
    public function addFooter($content)
    {
        self::$share['footer'][] = $content;
    }

    /**
     * get Header
     */
    public function getFooter()
    {
        return !empty(self::$share['footer']) ? chr(13).chr(9).implode(chr(13).chr(9),self::$share['footer']) : null ;
    }
}