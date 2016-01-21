<?php
namespace Cyan\Library;

/**
 * Class Theme
 * @package Cyan\Library
 * @since 1.0.0
 *
 * @method Theme getInstance
 */
class Theme extends View
{
    use TraitSingleton;

    /**
     * Theme Folder
     *
     * @var string
     * @since 1.0.0
     */
    protected $folder;

    /**
     * Static global Data
     *
     * @var array
     * @since 1.0.0
     */
    protected static $share = [
        'header' => [],
        'footer' => []
    ];

    /**
     * Add custom string to head position
     *
     * @param $content
     *
     * @since 1.0.0
     */
    public function addHeader($content)
    {
        self::$share['header'][] = $content;
    }

    /**
     * Render Header
     *
     * @return string|null
     *
     * @since 1.0.0
     */
    public function getHeader()
    {
        return !empty(self::$share['header']) ? chr(13).chr(9).implode(chr(13).chr(9),self::$share['header']) : null ;
    }

    /**
     * Add custom string to footer position
     *
     * @param string $content
     *
     * @since 1.0.0
     */
    public function addFooter($content)
    {
        self::$share['footer'][] = $content;
    }

    /**
     * Return folder
     *
     * @return string
     *
     * @since 1.0.0
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Render Footer
     *
     * @return string|null
     *
     * @since 1.0.0
     */
    public function getFooter()
    {
        return !empty(self::$share['footer']) ? chr(13).chr(9).implode(chr(13).chr(9),self::$share['footer']) : null ;
    }
}