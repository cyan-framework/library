<?php
namespace Cyan\Library;

/**
 * Class Layout
 * @package Cyan\Library
 * @since 1.0.0
 */
class Layout
{
    use TraitMultiton, TraitFilepath, TraitContainer;

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
        return self::getInstance($layout,$layout, $data, $options)->render();
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
     * @return Config
     *
     * @since 1.0.0
     */
    public function getOptions()
    {
        return $this->options;
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