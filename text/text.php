<?php
namespace Cyan\Library;

/**
 * Class Text
 * @package Cyan\Library
 */
class Text
{
    /**
     * Traits
     */
    use TraitsSingleton;

    /**
     * Strings
     *
     * @var array
     */
    private $strings = [];

    /**
     * Translate a string
     *
     * @param $string
     *
     * @return mixed
     */
    public function translate($string)
    {
        return isset($this->strings[$string]) ? $this->strings[$string] : $string ;
    }

    /**
     * Translate string using sprintf
     *
     * @return string
     */
    public function sprintf()
    {
        $args = func_get_args();
        foreach ($args as &$string) {
            $string = isset($this->strings[$string]) ? $this->strings[$string] : $string ;
        }

        return call_user_func_array('sprintf', $args);
    }

    /**
     * Load a language
     *
     * @param $lang_code
     * @param string $identifier_base
     * @return bool
     */
    public function loadLanguage($lang_code, $identifier_base = 'app:language')
    {
        $langIdentifier = $identifier_base.'.'.$lang_code.'.'.$lang_code;
        return $this->loadLanguageIdentifier($langIdentifier);
    }

    /**
     * Load a Language from Identifier
     *
     * @param $langIdentifier
     * @return bool
     */
    public function loadLanguageIdentifier($langIdentifier)
    {
        $lang_path = Finder::getInstance()->getPath($langIdentifier,'.ini');
        if (file_exists($lang_path)) {
            $this->strings = empty($this->strings) ? parse_ini_file($lang_path) : array_merge($this->strings, parse_ini_file($lang_path));
            return true;
        }

        return false;
    }
}