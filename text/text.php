<?php
namespace Cyan\Framework;

/**
 * Class Text
 * @package Cyan\Framework
 * @since 1.0.0
 *
 * @method Text getInstance
 */
class Text
{
    use TraitSingleton;

    /**
     * Strings
     *
     * @var array
     * @since 1.0.0
     */
    private $strings = [];

    /**
     * Translate a string
     *
     * @param $string
     *
     * @return mixed
     *
     * @since 1.0.0
     */
    public function translate($string)
    {
        return isset($this->strings[$string]) ? $this->strings[$string] : $string ;
    }

    /**
     * Translate string using sprintf
     *
     * @return string
     *
     * @since 1.0.0
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
     * @param string $lang_code
     * @param string $identifier_base
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function loadLanguage($lang_code, $identifier_base)
    {
        $langIdentifier = $identifier_base.$lang_code.'.'.$lang_code;
        return $this->loadLanguageIdentifier($langIdentifier);
    }

    /**
     * Load a Language from Identifier
     *
     * @param string $langIdentifier
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public function loadLanguageIdentifier($langIdentifier, $suffix='')
    {
        $lang_path = Finder::getInstance()->getPath($langIdentifier,$suffix.'.ini');
        if (file_exists($lang_path)) {
            $this->strings = empty($this->strings) ? parse_ini_file($lang_path) : array_merge($this->strings, parse_ini_file($lang_path));
            return true;
        }

        return false;
    }
}