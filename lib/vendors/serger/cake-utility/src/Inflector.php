<?php
/**
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @license MIT
 */

namespace SergeR\CakeUtility;

/**
 * Class Inflector
 * @package SergeR\CakeUtility\Inflector
 */
class Inflector
{
    /**
     * Plural inflector rules
     *
     * @var array
     */
    protected static $_plural = [
        '/(s)tatus$/i' => '\1tatuses',
        '/(quiz)$/i' => '\1zes',
        '/^(ox)$/i' => '\1\2en',
        '/([m|l])ouse$/i' => '\1ice',
        '/(matr|vert|ind)(ix|ex)$/i' => '\1ices',
        '/(x|ch|ss|sh)$/i' => '\1es',
        '/([^aeiouy]|qu)y$/i' => '\1ies',
        '/(hive)$/i' => '\1s',
        '/(chef)$/i' => '\1s',
        '/(?:([^f])fe|([lre])f)$/i' => '\1\2ves',
        '/sis$/i' => 'ses',
        '/([ti])um$/i' => '\1a',
        '/(p)erson$/i' => '\1eople',
        '/(?<!u)(m)an$/i' => '\1en',
        '/(c)hild$/i' => '\1hildren',
        '/(buffal|tomat)o$/i' => '\1\2oes',
        '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin)us$/i' => '\1i',
        '/us$/i' => 'uses',
        '/(alias)$/i' => '\1es',
        '/(ax|cris|test)is$/i' => '\1es',
        '/s$/' => 's',
        '/^$/' => '',
        '/$/' => 's',
    ];
    /**
     * Singular inflector rules
     *
     * @var array
     */
    protected static $_singular = [
        '/(s)tatuses$/i' => '\1\2tatus',
        '/^(.*)(menu)s$/i' => '\1\2',
        '/(quiz)zes$/i' => '\\1',
        '/(matr)ices$/i' => '\1ix',
        '/(vert|ind)ices$/i' => '\1ex',
        '/^(ox)en/i' => '\1',
        '/(alias)(es)*$/i' => '\1',
        '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
        '/([ftw]ax)es/i' => '\1',
        '/(cris|ax|test)es$/i' => '\1is',
        '/(shoe)s$/i' => '\1',
        '/(o)es$/i' => '\1',
        '/ouses$/' => 'ouse',
        '/([^a])uses$/' => '\1us',
        '/([m|l])ice$/i' => '\1ouse',
        '/(x|ch|ss|sh)es$/i' => '\1',
        '/(m)ovies$/i' => '\1\2ovie',
        '/(s)eries$/i' => '\1\2eries',
        '/([^aeiouy]|qu)ies$/i' => '\1y',
        '/(tive)s$/i' => '\1',
        '/(hive)s$/i' => '\1',
        '/(drive)s$/i' => '\1',
        '/([le])ves$/i' => '\1f',
        '/([^rfoa])ves$/i' => '\1fe',
        '/(^analy)ses$/i' => '\1sis',
        '/(analy|diagno|^ba|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
        '/([ti])a$/i' => '\1um',
        '/(p)eople$/i' => '\1\2erson',
        '/(m)en$/i' => '\1an',
        '/(c)hildren$/i' => '\1\2hild',
        '/(n)ews$/i' => '\1\2ews',
        '/eaus$/' => 'eau',
        '/^(.*us)$/' => '\\1',
        '/s$/i' => ''
    ];
    /**
     * Irregular rules
     *
     * @var array
     */
    protected static $_irregular = [
        'atlas' => 'atlases',
        'beef' => 'beefs',
        'brief' => 'briefs',
        'brother' => 'brothers',
        'cafe' => 'cafes',
        'child' => 'children',
        'cookie' => 'cookies',
        'corpus' => 'corpuses',
        'cow' => 'cows',
        'criterion' => 'criteria',
        'ganglion' => 'ganglions',
        'genie' => 'genies',
        'genus' => 'genera',
        'graffito' => 'graffiti',
        'hoof' => 'hoofs',
        'loaf' => 'loaves',
        'man' => 'men',
        'money' => 'monies',
        'mongoose' => 'mongooses',
        'move' => 'moves',
        'mythos' => 'mythoi',
        'niche' => 'niches',
        'numen' => 'numina',
        'occiput' => 'occiputs',
        'octopus' => 'octopuses',
        'opus' => 'opuses',
        'ox' => 'oxen',
        'penis' => 'penises',
        'person' => 'people',
        'sex' => 'sexes',
        'soliloquy' => 'soliloquies',
        'testis' => 'testes',
        'trilby' => 'trilbys',
        'turf' => 'turfs',
        'potato' => 'potatoes',
        'hero' => 'heroes',
        'tooth' => 'teeth',
        'goose' => 'geese',
        'foot' => 'feet',
        'foe' => 'foes',
        'sieve' => 'sieves'
    ];
    /**
     * Words that should not be inflected
     *
     * @var array
     */
    protected static $_uninflected = [
        '.*[nrlm]ese', '.*data', '.*deer', '.*fish', '.*measles', '.*ois',
        '.*pox', '.*sheep', 'people', 'feedback', 'stadia', '.*?media',
        'chassis', 'clippers', 'debris', 'diabetes', 'equipment', 'gallows',
        'graffiti', 'headquarters', 'information', 'innings', 'news', 'nexus',
        'pokemon', 'proceedings', 'research', 'sea[- ]bass', 'series', 'species', 'weather'
    ];
    /**
     * Default map of accented and special characters to ASCII characters
     *
     * @var array
     */
    protected static $_transliteration = [
        '??' => 'ae',
        '??' => 'ae',
        '??' => 'ae',
        '??' => 'oe',
        '??' => 'oe',
        '??' => 'ue',
        '??' => 'Ae',
        '??' => 'Ue',
        '??' => 'Oe',
        '??' => 'A',
        '??' => 'A',
        '??' => 'A',
        '??' => 'A',
        '??' => 'A',
        '??' => 'A',
        '??' => 'A',
        '??' => 'A',
        '??' => 'A',
        '??' => 'A',
        '??' => 'a',
        '??' => 'a',
        '??' => 'a',
        '??' => 'a',
        '??' => 'a',
        '??' => 'a',
        '??' => 'a',
        '??' => 'a',
        '??' => 'a',
        '??' => 'a',
        '??' => 'a',
        '??' => 'C',
        '??' => 'C',
        '??' => 'C',
        '??' => 'C',
        '??' => 'C',
        '??' => 'c',
        '??' => 'c',
        '??' => 'c',
        '??' => 'c',
        '??' => 'c',
        '??' => 'D',
        '??' => 'D',
        '??' => 'D',
        '??' => 'd',
        '??' => 'd',
        '??' => 'd',
        '??' => 'E',
        '??' => 'E',
        '??' => 'E',
        '??' => 'E',
        '??' => 'E',
        '??' => 'E',
        '??' => 'E',
        '??' => 'E',
        '??' => 'E',
        '??' => 'e',
        '??' => 'e',
        '??' => 'e',
        '??' => 'e',
        '??' => 'e',
        '??' => 'e',
        '??' => 'e',
        '??' => 'e',
        '??' => 'e',
        '??' => 'G',
        '??' => 'G',
        '??' => 'G',
        '??' => 'G',
        '??' => 'G',
        '??' => 'g',
        '??' => 'g',
        '??' => 'g',
        '??' => 'g',
        '??' => 'g',
        '??' => 'H',
        '??' => 'H',
        '??' => 'h',
        '??' => 'h',
        '??' => 'I',
        '??' => 'I',
        '??' => 'I',
        '??' => 'I',
        '??' => 'Yi',
        '??' => 'I',
        '??' => 'I',
        '??' => 'I',
        '??' => 'I',
        '??' => 'I',
        '??' => 'I',
        '??' => 'I',
        '??' => 'i',
        '??' => 'i',
        '??' => 'i',
        '??' => 'i',
        '??' => 'i',
        '??' => 'yi',
        '??' => 'i',
        '??' => 'i',
        '??' => 'i',
        '??' => 'i',
        '??' => 'i',
        '??' => 'i',
        '??' => 'J',
        '??' => 'j',
        '??' => 'K',
        '??' => 'k',
        '??' => 'L',
        '??' => 'L',
        '??' => 'L',
        '??' => 'L',
        '??' => 'L',
        '??' => 'l',
        '??' => 'l',
        '??' => 'l',
        '??' => 'l',
        '??' => 'l',
        '??' => 'N',
        '??' => 'N',
        '??' => 'N',
        '??' => 'N',
        '??' => 'n',
        '??' => 'n',
        '??' => 'n',
        '??' => 'n',
        '??' => 'n',
        '??' => 'O',
        '??' => 'O',
        '??' => 'O',
        '??' => 'O',
        '??' => 'O',
        '??' => 'O',
        '??' => 'O',
        '??' => 'O',
        '??' => 'O',
        '??' => 'O',
        '??' => 'O',
        '??' => 'o',
        '??' => 'o',
        '??' => 'o',
        '??' => 'o',
        '??' => 'o',
        '??' => 'o',
        '??' => 'o',
        '??' => 'o',
        '??' => 'o',
        '??' => 'o',
        '??' => 'o',
        '??' => 'o',
        '??' => 'R',
        '??' => 'R',
        '??' => 'R',
        '??' => 'r',
        '??' => 'r',
        '??' => 'r',
        '??' => 'S',
        '??' => 'S',
        '??' => 'S',
        '??' => 'S',
        '??' => 'S',
        '???' => 'SS',
        '??' => 's',
        '??' => 's',
        '??' => 's',
        '??' => 's',
        '??' => 's',
        '??' => 's',
        '??' => 'T',
        '??' => 'T',
        '??' => 'T',
        '??' => 'T',
        '??' => 't',
        '??' => 't',
        '??' => 't',
        '??' => 't',
        '??' => 'U',
        '??' => 'U',
        '??' => 'U',
        '??' => 'U',
        '??' => 'U',
        '??' => 'U',
        '??' => 'U',
        '??' => 'U',
        '??' => 'U',
        '??' => 'U',
        '??' => 'U',
        '??' => 'U',
        '??' => 'U',
        '??' => 'U',
        '??' => 'U',
        '??' => 'u',
        '??' => 'u',
        '??' => 'u',
        '??' => 'u',
        '??' => 'u',
        '??' => 'u',
        '??' => 'u',
        '??' => 'u',
        '??' => 'u',
        '??' => 'u',
        '??' => 'u',
        '??' => 'u',
        '??' => 'u',
        '??' => 'u',
        '??' => 'u',
        '??' => 'Y',
        '??' => 'Y',
        '??' => 'Y',
        '??' => 'y',
        '??' => 'y',
        '??' => 'y',
        '??' => 'W',
        '??' => 'w',
        '??' => 'Z',
        '??' => 'Z',
        '??' => 'Z',
        '??' => 'z',
        '??' => 'z',
        '??' => 'z',
        '??' => 'AE',
        '??' => 'AE',
        '??' => 'ss',
        '??' => 'IJ',
        '??' => 'ij',
        '??' => 'OE',
        '??' => 'f',
        '??' => 'TH',
        '??' => 'th',
        '??' => 'Ye',
        '??' => 'ye',
    ];
    /**
     * Method cache array.
     *
     * @var array
     */
    protected static $_cache = [];

    /**
     * The initial state of Inflector so reset() works.
     *
     * @var array
     */
    protected static $_initialState = [];

    /**
     * Cache inflected values, and return if already available
     *
     * @param string $type Inflection type
     * @param string $key Original value
     * @param string|bool $value Inflected value
     * @return string|bool Inflected value on cache hit or false on cache miss.
     */
    protected static function _cache($type, $key, $value = false)
    {
        $key = '_' . $key;
        $type = '_' . $type;
        if ($value !== false) {
            static::$_cache[$type][$key] = $value;
            return $value;
        }
        if (!isset(static::$_cache[$type][$key])) {
            return false;
        }
        return static::$_cache[$type][$key];
    }

    /**
     * Clears Inflectors inflected value caches. And resets the inflection
     * rules to the initial values.
     *
     * @return void
     */
    public static function reset()
    {
        if (empty(static::$_initialState)) {
            static::$_initialState = get_class_vars(__CLASS__);
            return;
        }
        foreach (static::$_initialState as $key => $val) {
            if ($key !== '_initialState') {
                static::${$key} = $val;
            }
        }
    }

    /**
     * Adds custom inflection $rules, of either 'plural', 'singular',
     * 'uninflected', 'irregular' or 'transliteration' $type.
     *
     * ### Usage:
     *
     * ```
     * Inflector::rules('plural', ['/^(inflect)or$/i' => '\1ables']);
     * Inflector::rules('irregular', ['red' => 'redlings']);
     * Inflector::rules('uninflected', ['dontinflectme']);
     * Inflector::rules('transliteration', ['/??/' => 'aa']);
     * ```
     *
     * @param string $type The type of inflection, either 'plural', 'singular',
     *   'uninflected' or 'transliteration'.
     * @param array $rules Array of rules to be added.
     * @param bool $reset If true, will unset default inflections for all
     *        new rules that are being defined in $rules.
     * @return void
     */
    public static function rules($type, $rules, $reset = false)
    {
        $var = '_' . $type;
        if ($reset) {
            static::${$var} = $rules;
        } elseif ($type === 'uninflected') {
            static::$_uninflected = array_merge(
                $rules,
                static::$_uninflected
            );
        } else {
            static::${$var} = $rules + static::${$var};
        }
        static::$_cache = [];
    }

    /**
     * Return $word in plural form.
     *
     * @param string $word Word in singular
     * @return string Word in plural
     */
    public static function pluralize($word)
    {
        if (isset(static::$_cache['pluralize'][$word])) {
            return static::$_cache['pluralize'][$word];
        }
        if (!isset(static::$_cache['irregular']['pluralize'])) {
            static::$_cache['irregular']['pluralize'] = '(?:' . implode('|', array_keys(static::$_irregular)) . ')';
        }
        if (preg_match('/(.*?(?:\\b|_))(' . static::$_cache['irregular']['pluralize'] . ')$/i', $word, $regs)) {
            static::$_cache['pluralize'][$word] = $regs[1] . substr($regs[2], 0, 1) .
                substr(static::$_irregular[strtolower($regs[2])], 1);
            return static::$_cache['pluralize'][$word];
        }
        if (!isset(static::$_cache['uninflected'])) {
            static::$_cache['uninflected'] = '(?:' . implode('|', static::$_uninflected) . ')';
        }
        if (preg_match('/^(' . static::$_cache['uninflected'] . ')$/i', $word, $regs)) {
            static::$_cache['pluralize'][$word] = $word;
            return $word;
        }
        foreach (static::$_plural as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                static::$_cache['pluralize'][$word] = preg_replace($rule, $replacement, $word);
                return static::$_cache['pluralize'][$word];
            }
        }
    }

    /**
     * Return $word in singular form.
     *
     * @param string $word Word in plural
     * @return string Word in singular
     */
    public static function singularize($word)
    {
        if (isset(static::$_cache['singularize'][$word])) {
            return static::$_cache['singularize'][$word];
        }
        if (!isset(static::$_cache['irregular']['singular'])) {
            static::$_cache['irregular']['singular'] = '(?:' . implode('|', static::$_irregular) . ')';
        }
        if (preg_match('/(.*?(?:\\b|_))(' . static::$_cache['irregular']['singular'] . ')$/i', $word, $regs)) {
            static::$_cache['singularize'][$word] = $regs[1] . substr($regs[2], 0, 1) .
                substr(array_search(strtolower($regs[2]), static::$_irregular), 1);
            return static::$_cache['singularize'][$word];
        }
        if (!isset(static::$_cache['uninflected'])) {
            static::$_cache['uninflected'] = '(?:' . implode('|', static::$_uninflected) . ')';
        }
        if (preg_match('/^(' . static::$_cache['uninflected'] . ')$/i', $word, $regs)) {
            static::$_cache['pluralize'][$word] = $word;
            return $word;
        }
        foreach (static::$_singular as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                static::$_cache['singularize'][$word] = preg_replace($rule, $replacement, $word);
                return static::$_cache['singularize'][$word];
            }
        }
        static::$_cache['singularize'][$word] = $word;
        return $word;
    }

    /**
     * Returns the input lower_case_delimited_string as a CamelCasedString.
     *
     * @param string $string String to camelize
     * @param string $delimiter the delimiter in the input string
     * @return string CamelizedStringLikeThis.
     */
    public static function camelize($string, $delimiter = '_')
    {
        $cacheKey = __FUNCTION__ . $delimiter;
        $result = static::_cache($cacheKey, $string);
        if ($result === false) {
            $result = str_replace(' ', '', static::humanize($string, $delimiter));
            static::_cache($cacheKey, $string, $result);
        }
        return $result;
    }

    /**
     * Returns the input CamelCasedString as an underscored_string.
     *
     * Also replaces dashes with underscores
     *
     * @param string $string CamelCasedString to be "underscorized"
     * @return string underscore_version of the input string
     */
    public static function underscore($string)
    {
        return static::delimit(str_replace('-', '_', $string), '_');
    }

    /**
     * Returns the input CamelCasedString as an dashed-string.
     *
     * Also replaces underscores with dashes
     *
     * @param string $string The string to dasherize.
     * @return string Dashed version of the input string
     */
    public static function dasherize($string)
    {
        return static::delimit(str_replace('_', '-', $string), '-');
    }

    /**
     * Returns the input lower_case_delimited_string as 'A Human Readable String'.
     * (Underscores are replaced by spaces and capitalized following words.)
     *
     * @param string $string String to be humanized
     * @param string $delimiter the character to replace with a space
     * @return string Human-readable string
     */
    public static function humanize($string, $delimiter = '_')
    {
        $cacheKey = __FUNCTION__ . $delimiter;
        $result = static::_cache($cacheKey, $string);
        if ($result === false) {
            $result = explode(' ', str_replace($delimiter, ' ', $string));
            foreach ($result as &$word) {
                $word = mb_strtoupper(mb_substr($word, 0, 1)) . mb_substr($word, 1);
            }
            $result = implode(' ', $result);
            static::_cache($cacheKey, $string, $result);
        }
        return $result;
    }
    /**
     * Expects a CamelCasedInputString, and produces a lower_case_delimited_string
     *
     * @param string $string String to delimit
     * @param string $delimiter the character to use as a delimiter
     * @return string delimited string
     */
    public static function delimit($string, $delimiter = '_')
    {
        $cacheKey = __FUNCTION__ . $delimiter;
        $result = static::_cache($cacheKey, $string);
        if ($result === false) {
            $result = mb_strtolower(preg_replace('/(?<=\\w)([A-Z])/', $delimiter . '\\1', $string));
            static::_cache($cacheKey, $string, $result);
        }
        return $result;
    }

    /**
     * Returns camelBacked version of an underscored string.
     *
     * @param string $string String to convert.
     * @return string in variable form
     */
    public static function variable($string)
    {
        $result = static::_cache(__FUNCTION__, $string);
        if ($result === false) {
            $camelized = static::camelize(static::underscore($string));
            $replace = strtolower(substr($camelized, 0, 1));
            $result = $replace . substr($camelized, 1);
            static::_cache(__FUNCTION__, $string, $result);
        }
        return $result;
    }
}