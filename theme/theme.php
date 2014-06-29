<?php
namespace Cyan\Library;

class Theme
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
     * View Constructor
     *
     * @param array $config
     */
    final public function __construct(array $config = array())
    {
        $finder = Finder::getInstance();
        $app = \Cyan::initialize()->Application->current;

        $default_path = \Cyan::initialize()->getRootPath() . DIRECTORY_SEPARATOR . 'theme' ;
        if ($finder->hasResource('app') && !isset($config['path'])) {
            $config['path'] = $finder->getPath('app:theme');
        }
        $this->_path = isset($config['path']) ? $config['path'] : $default_path ;

        if (isset($config['theme'])) {
            $this->tpl($config['theme']);
        } else {
            $default_app_template = $default_path.'/application/application.php';
            $app_template = $this->_path.'/application/application.php';
            if (file_exists($default_app_template)) {
                $this->tpl('application','application');
            } elseif (file_exists($app_template)) {
                $this->tpl('application','application');
            }
        }

        if ($app instanceof Application) {
            $app_config = $app->getConfig();

            if (substr($app->Router->base,-4) === '.php' {
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
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = array_merge($this->data, $data);

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
    final public function tpl($folder, $layout = null, $path = null)
    {
        if (is_null($folder)) {
            throw new ThemeException('Folder cant be null');
        }

        $layout = is_null($layout) ? 'index' : $layout ;
        $base_path = !is_null($path) ? $path : $this->_path ;
        $view_path = sprintf('%s/%s', $base_path, $folder);
        $layout_path = sprintf('%s/%s.php', $view_path, $layout);

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
     * Return link
     *
     * @param $uri
     * @param array $config
     */
    public function link_to($uri, array $config = array())
    {
        return \Cyan::initialize()->Application->current->Router->link_to($uri);
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
        if (!empty($this->layout_path) && is_readable($this->layout_path) & file_exists($this->layout_path)) {
            ob_start();
            include $this->layout_path;
            $this->_content = ob_get_clean();
        } else {
            if (empty($this->_content)) {
                $this->_content = $this->data['outlet'];
            }
        }

        $this->trigger('Render', $this);

        return $this->_content;
    }

    /**
     * Output a view
     */
    public function __toString()
    {
        return $this->render();
    }
}