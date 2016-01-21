<?php
namespace Cyan\Library;

/**
 * Class View
 * @package Cyan\Library
 * @since 1.0.0
 */
class View
{
    use TraitContainer, TraitEvent;

    /**
     * View base path
     *
     * @var string
     * @since 1.0.0
     */
    protected $base_path;

    /**
     * Layout file path
     *
     * @var string
     * @since 1.0.0
     */
    protected $layout_path;

    /**
     * View Folder
     *
     * @var string
     * @since 1.0.0
     */
    protected $folder;

    /**
     * Layout File
     *
     * @var string
     * @since 1.0.0
     */
    protected $layout;

    /**
     * Data Mapper
     *
     * @var array
     * @since 1.0.0
     */
    protected $data = [];

    /**
     * View Constructor
     *
     * @param array $config
     *
     * @since 1.0.0
     */
    public function __construct(array $config = [])
    {
        if (isset($config['folder'])) {
            $this->setFolder($config['folder']);
        }

        if (isset($config['layout'])) {
            $this->setLayout($config['layout']);
        }

        if (isset($config['data'])) {
            $this->setData($config['data']);
        }

        if (isset($config['base_path'])) {
            $this->setBasePath($config['base_path']);
        }
    }

    /**
     * initialize plugin
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function initialize()
    {
        //import view plugins
        $this->getContainer('factory_plugin')->assign('view', $this);
        $this->trigger('Initialize', $this);

        return $this;
    }

    /**
     * Set view base path
     *
     * @param $path
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setBasePath($path)
    {
        $this->base_path = $path;

        return $this;
    }

    /**
     * Get view base path
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getBasePath()
    {
        return $this->base_path;
    }

    /**
     * Set Data
     *
     * @param string $key
     * @param $value
     *
     * @return $this
     *
     * @since 1.0.0
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
     *
     * @return mixed|null
     *
     * @since 1.0.0
     */
    public function get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null ;
    }

    /**
     * Define array of data
     *
     * @param array $data
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setData(array $data)
    {
        $this->data = array_merge($this->data,$data);

        return $this;
    }

    /**
     * Get content from view
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getContent()
    {
        return $this->buffer_content;
    }

    /**
     * Set content from view
     *
     * @param string $content
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setContent($content)
    {
        $this->buffer_content = $content;

        return $this;
    }

    /**
     * Render Layout
     *
     * @param string $folder
     * @param string $layout
     * @param string $path
     *
     * @return $this
     *
     * @since 1.0.0
     * @throws ViewException
     */
    public function tpl($folder, $layout = null, $path = null)
    {
        if (is_null($folder)) {
            throw new ViewException('Folder cant be null');
        }

        $layout = is_null($layout) ? 'index' : $layout ;
        $base_path = is_null($path) ? $this->base_path : $path ;
        $view_path = sprintf('%s/%s', $base_path, $folder);
        $layout_path = sprintf('%s/%s.php', $view_path, $layout);

        if (empty($folder) || !is_dir($view_path)) {
            throw new ViewException(sprintf('View folder path "%s" not found',$view_path));
        }

        if (!file_exists($layout_path)) {
            throw new ViewException(sprintf('View Layout not found "%s"', $layout_path));
        }

        $this->layout_path = $layout_path;

        return $this;
    }

    /**
     * Set view Folder
     *
     * @param string $folder
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * @return string
     *
     * @since 1.0.0
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Set Layout
     *
     * @param $layout
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Render a layout
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function display()
    {
        if (is_null($this->layout_path) && !empty($this->folder) && !empty($this->layout)) {
            $this->tpl($this->folder, $this->layout);
        }

        $Cyan = \Cyan::initialize();
        ob_start();
        include $this->layout_path;
        $this->buffer_content = ob_get_clean();
        $this->trigger('Render', $this);

        return $this->buffer_content;
    }
}