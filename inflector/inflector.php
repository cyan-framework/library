<?php
namespace Cyan\Library;

/**
 * Class Inflector
 * @package Cyan\Library
 */
abstract class Inflector
{
	/**
 	 * Cache of pluralized and singularized nouns.
	 *
	 * @var array
     */
	protected static $_cache = array(
		'singularized' => array(),
		'pluralized'   => array()
	);

	/**
	 * Rules for pluralization, singularization
	 * 
	 * @var array
	 */
	protected static $_rules = array(
		'pluralization' => array(),
        'singularization' => array()
	);

    /**
     * define rules
     *
     * @param $type
     * @param $rule
     */
	public static function setRules($type, $rule)
	{
		if (array_key_exists($type, self::$_rules))
		{
			if (is_array($rule))
			{
				self::$_rules[$type] = array_merge(self::$_rules[$type], $rule);
			}
			else if (is_string($rule))
			{
				array_push(self::$_rules[$type], $rule);
			}
		}
	}

    /**
     * @param $word
     * @return string
     */
	public static function humanize($word)
	{
		$result = strtolower(str_replace("_", " ", $word));
		return $result;
	}

    /**
     * @param $word
     * @return mixed|string
     */
	public static function underscore($word)
	{
		$word = preg_replace('/(\s)+/', '_', $word);
		$word = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $word));
		return $word;
	}

    /**
     * @param $word
     * @return mixed
     */
	public static function camelize($word)
	{
		$word = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $word);
		$word = str_replace(' ', '', ucwords(strtolower(str_replace('_', ' ', $word))));
		return $word;
	}

    /**
     * @param $word
     * @return mixed|string
     */
	public static function pluralize($word)
	{
	   $word = strval($word);
		//Get the cached noun of it exists
 	   	if(array_key_exists((string)$word,self::$_cache['pluralized']) !== false) {
			return self::$_cache['pluralized'][$word];
 	   	}
 	   	
 	   	foreach (self::$_rules['pluralization'] as $regexp => $replacement)
		{
			$matches = null;
			$plural = preg_replace($regexp, $replacement, $word, -1, $matches);
			if ($matches > 0) {
                self::addWord($word,$plural);
				return $plural;
			}
		}

		return $word;
	}

    /**
     * @param $word
     * @return mixed
     */
	public static function singularize($word)
	{
		//Get the cached noun of it exists
 	   	if(array_key_exists((string)$word,self::$_cache['singularized']) !== false) {
			return self::$_cache['singularized'][$word];
 	   	}
 	   	
 	   	if (empty(self::$_rules['singularization'])) {
			foreach (self::$_rules['singularization'] as $regexp => $replacement)
			{
				$matches = null;
				$plural = preg_replace($regexp, $replacement, $word, -1, $matches);
				if ($matches > 0) {
	                self::addWord($word,$plural);
					return $plural;
				}
			}
 	   	}
 	   	
 	   	return $word;
	}
	
	/**
	 * Check to see if an word is singular
	 *
	 * @param string $string The word to check
	 * @return boolean
	 */
	public static function isSingular($string) {
		return self::singularize(self::pluralize($string)) == $string;
	}

	/**
	 * Check to see if an word is plural
	 *
	 * @param string $string
	 * @return boolean
	 */
	public static function isPlural($plural) {
		return self::pluralize(self::singularize($plural)) == $plural;
	}
	
	/**
	 * Singular word to plural.
	 * 
	 * @param string $singular
	 * @param string $plural
	 */
	public static function addWord($singular, $plural)
	{
        $singular = strval($singular);
        $plural = strval($plural);
		self::$_cache['pluralized'][$singular]	= $plural;
		self::$_cache['singularized'][$plural] 	= $singular;
	}
}