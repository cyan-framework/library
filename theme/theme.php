<?php
namespace Cyan\Library;

/**
 * Class Theme
 * @package Cyan\Library
 */
class Theme extends View
{
    /**
     * View Constructor
     *
     * @param array $config
     */
    public function __construct(array $config = [])
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

            if (substr($app->Router->base,-4) === '.php') {
                $base_url = str_replace(basename($app->Router->base),'',$app->Router->base);
            } else {
                $base_url = $app->Router->base;
            }

            $this->set('base_url', $app->Router->base);
            $this->set('assets_url', rtrim($base_url));
            $this->set('title', isset($app_config['title']) ? $app_config['title'] : $app->getName() );
            $this->set('app_name', isset($app_config['app_name']) ? $app_config['app_name'] : $app->getName() );
        }

        $this->trigger('Initialize', $this);
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
     * Render a layout
     */
    final public function render()
    {
        global $Cyan;
        $App = $Cyan->Application->current;
        $Router = $App->Router;
        $Text = $App->Text;

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
        $this->share['header'][] = $content;
        $this->set('head', implode(chr(13).chr(9),$this->share['header']));
    }
}