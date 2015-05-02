<?php
namespace Cyan\Library;

/**
 * Class Text
 * @package Cyan\Library
 */
class Text
{
    /**
     * Translate a string
     *
     * @param $string
     *
     * @return mixed
     */
    public function translate($string)
    {
        return isset($this->texts[$string]) ? $this->texts[$string] : $string ;
    }

    /**
     * Translate string using sprintf
     *
     * @return string
     */
    public function sprintf()
    {
        $args = func_get_args();
        $string = array_shift($args);
        $string = isset($this->texts[$string]) ? $this->texts[$string] : $string ;

        return sprintf($string,$args);
    }

    /**
     * Load a language
     *
     * @param $lang_code
     * @param $identifier_base
     *
     */
    public function loadLanguage($lang_code, $identifier_base = 'app:language')
    {
        $lang_path = Finder::getInstance()->getPath($identifier_base.'.'.$lang_code.'.'.$lang_code,'.ini');
        if (!file_exists($lang_path)) {
            throw new TextException(sprintf('The language "%s" was not found in your app language path.',$lang_code));
        }

        $this->strings = empty($this->strings) ? parse_ini_file($lang_path, true) : array_merge($this->strings, parse_ini_file($lang_path, true));
    }
}