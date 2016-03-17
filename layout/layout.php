<?php
namespace Cyan\Library;

/**
 * Class Layout
 * @package Cyan\Library
 * @since 1.0.0
 */
class Layout
{
    use TraitFilepath, TraitContainer;

    /**
     * @var string
     * @since 1.0.0
     */
    protected $identifier;

    /**
     * @var Config
     * @since 1.0.0
     */
    protected $data;

    /**
     * @var Config
     * @since 1.0.0
     */
    protected $options;

    /**
     * Singleton instances accessible by array key
     */
    protected static $instances = [];

    /**
     * Return an instance of the class.
     */
    public static function getInstance($name = null)
    {
        $args = array_slice(func_get_args(), 1);
        $name = $name ?: 'default';
        $static = get_called_class();
        $key = sprintf('%s::%s', $static, $name);
        if(!array_key_exists($key, static::$instances))
        {
            static::$instances[$key] = new self($name, []);
        }

        if (isset($args[0])) {
            static::$instances[$key]->setData($args[0], true);
        }
        if (isset($args[1])) {
            static::$instances[$key]->setOptions($args[1], true);
        }

        return static::$instances[$key];
    }

    /**
     * Layout constructor.
     *
     * @param string $layout
     * @param Config $data
     * @param Config $options
     *
     * @since 1.0.0
     */
    public function __construct($layout, array $data, array $options = [])
    {
        if (empty($layout)) {
            throw new LayoutException('Layout must be not empty');
        }
        $this->options = Config::getInstance($layout.'#options');
        $this->options->loadArray($options);

        $this->data = Config::getInstance($layout.'#data');
        $this->data->loadArray($data);

        $this->layout = $layout;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     *
     * @since 1.0.0
     */
    public function set($key, $value)
    {
        return $this->data->set($key, $value);
    }

    /**
     * @param $key
     * @param null $default_value
     *
     * @return array|null|string
     *
     * @since 1.0.0
     */
    public function get($key, $default_value = null)
    {
        return $this->data->get($key, $default_value);
    }

    /**
     * @param $key
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function exists($key)
    {
        return $this->exists($key);
    }

    /**
     * @param $key
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function remove($key)
    {
        return $this->data->remove($key);
    }

    /**
     * Render layout
     *
     * @param $layout
     * @param array $data
     * @param array $options
     *
     * @return string
     *
     * @since 1.0.0
     */
    public static function display($layout, array $data, array $options = [])
    {
        return self::getInstance($layout, $data, $options)->render();
    }

    /**
     * @return Config
     *
     * @since 1.0.0
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @param bool $override
     * @return $this
     */
    public function setData(array $data, $override = false)
    {
        if ($override) {
            $this->data->clear();
        }

        $this->data->loadArray($data);

        return $this;
    }

    /**
     * @return Config
     *
     * @since 1.0.0
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @param bool $override
     * @return $this
     */
    public function setOptions(array $options, $override = false)
    {
        if ($override) {
            $this->options->clear();
        }

        $this->options->loadArray($options);

        return $this;
    }

    /**
     * Return layout string
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Render layout
     *
     * @param null $layout
     * @param array $data
     * @param array $options
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function render($layout = null, array $data = [], array $options = [])
    {
        if (!empty($layout)) {
            return self::display($layout, $data, $options);
        }

        $output = '';
        $layout_path = str_replace('.',DIRECTORY_SEPARATOR,$this->layout).'.php';
        if ($file = FilesystemPath::find(self::addIncludePath(),$layout_path)) {
            $Cyan = \Cyan::initialize();
            ob_start();
            include $file;
            $output = ob_get_clean();
        }

        return $output;
    }
}