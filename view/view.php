<?php
namespace Cyan\Framework;

/**
 * Class View
 * @package Cyan\Framework
 * @since 1.0.0
 */
class View
{
    use TraitContainer, TraitEvent;

    /**
     * View Name
     *
     * @var string
     * @since 1.0.0
     */
    protected $name;

    /**
     * @var Layout
     * @since 1.0.0
     */
    protected $layout;

    /**
     * View Constructor
     *
     * @param array $config
     *
     * @since 1.0.0
     */
    public function __construct(array $config = [])
    {
        if (isset($config['name'])) {
            $this->name = $config['name'];
        }

        if (isset($config['data'])) {
            $this->setData($config['data']);
        }

        if (isset($config['base_path'])) {
            $this->setBasePath($config['base_path']);
        }

        if (isset($config['layout']) && empty($this->layout)) {
            $this->setLayout($config['layout']);
        } elseif(is_string($this->layout)) {
            $this->setLayout($this->layout);
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
        $this->layout->addPath($path);

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
        return $this->layout->addPath();
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
        if (!$this->layout instanceof Layout) {
        throw new ViewException(sprintf('View %s layout not started!',get_called_class()));
        }

        $this->layout->set($key, $value);

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
    public function get($key, $default_value = null)
    {
        return $this->layout->get($key, $default_value);
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
        $this->layout->setData($data);

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
        if (!is_string($layout)) {
            throw new ViewException(sprintf('Layout must be string, %s given.',gettype($layout)));
        }
        $this->layout = new Layout($layout,[]);
        if (!$this->layout->hasContainer('view')) {
            $this->layout->setContainer('view', $this);
        }
        if (!$this->layout->hasContainer('application') && $this->hasContainer('application')) {
            $this->layout->setContainer('application', $this->getContainer('application'));
        }

        return $this;
    }

    /**
     * Return name
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getName()
    {
        if (empty($this->name)) {
            $reflection = new ReflectionClass(__CLASS__);
            $self_class_name = strtolower($reflection->getShortName());

            $class_parts = explode('_', Inflector::underscore(get_called_class()));
            $class_name = implode(array_slice($class_parts, array_search($self_class_name,$class_parts) + 1));

            if (empty($class_name)) {
                $remove_parts = [];
                $remove_parts[] = $self_class_name;
                $remove_parts[] = strtolower($this->getContainer('application')->getName());
                $class_name = implode(array_unique(array_diff($class_parts, $remove_parts)));
            }

            $this->name = $class_name;
        }

        return $this->name;
    }

    /**
     * @return Layout
     *
     * @since 1.0.0
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Render a layout
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function display($layout = null)
    {
        $layout = !empty($layout) ? $layout : $this->layout;

        if (!($this->layout instanceof Layout)) {
            $this->setLayout($layout);
        }

        if (!$this->layout->hasContainer('view')) {
            $this->layout->setContainer('view', $this);
        }
        if (!$this->layout->hasContainer('application') && $this->hasContainer('application')) {
            $this->layout->setContainer('application', $this->getContainer('application'));
        }

        $Cyan = \Cyan::initialize();
        $this->buffer_content = $this->layout->render();
        $this->trigger('Render', $this);

        return $this->buffer_content;
    }
}