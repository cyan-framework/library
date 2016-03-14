<?php
namespace Cyan\Library;

/**
 * Class Layout
 * @package Cyan\Library
 */
class Layout
{
    use TraitMultiton, TraitFilepath, TraitContainer;

    /**
     * @var Config
     */
    protected $data;

    /**
     * @var Config
     */
    protected $options;

    /**
     * Layout constructor.
     * @param string $layout
     * @param Config $data
     * @param Config $options
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
     * @return $this
     */
    public function set($key, $value)
    {
        return $this->data->set($key, $value);
    }

    /**
     * @param $key
     * @param null $default_value
     * @return array|null|string
     */
    public function get($key, $default_value = null)
    {
        return $this->data->get($key, $default_value);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function exists($key)
    {
        return $this->exists($key);
    }

    /**
     * @param $key
     * @return bool
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
     */
    public static function render($layout, array $data, $options = [])
    {
        return self::getInstance($layout,$layout, $data, $options)->display();
    }

    /**
     * @return Config
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return Config
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function display()
    {
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