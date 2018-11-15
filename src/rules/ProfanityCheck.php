<?php

namespace owldesign\qarr\rules;

use Craft;
use craft\helpers\ArrayHelper;

class ProfanityCheck
{
    // Source
    // https://github.com/Askedio/laravel-profanity-filter

    protected $replaceWith = '';
    protected $badWords = [];
    protected $filterChecks = [];
    protected $replaceFullWords = true;
    protected $multiCharReplace = false;
    protected $strReplace = [];
    protected $replaceWithLength;
    protected $config = [];
    protected $filteredStrings = [];
    protected $wasFiltered = false;

    public function __construct($badWordsArray)
    {
        $this->config = $this->loadProfanitiesFromFile(CRAFT_VENDOR_PATH . '/owldesign/qarr/src/rules/data/config.php');
        $this->strReplace = $this->config['strReplace'];
        $this->reset();
        $this->badWords = array_merge(
            $this->config['defaults'],
            $badWordsArray
        );
        $this->generateFilterChecks();
    }
    public function reset()
    {
        $this->replaceFullWords($this->replaceFullWords);
        $this->replaceWith($this->replaceWith);
        return $this;
    }
    public function replaceWith($string)
    {
        $this->replaceWith = $string;
        $this->replaceWithLength = mb_strlen($this->replaceWith);
        $this->multiCharReplace = $this->replaceWithLength === 1;
        return $this;
    }
    public function replaceFullWords($boolean)
    {
        $this->replaceFullWords = $boolean;
        $this->generateFilterChecks();
        return $this;
    }
    private function resetFiltered()
    {
        $this->filteredStrings = [];
        $this->wasFiltered = false;
    }
    public function filter($string, $details = null)
    {
        $this->resetFiltered();
        if (!is_string($string) || !trim($string)) {
            return $string;
        }
        $filtered = $this->filterString($string);
        if ($details) {
            return [
                'orig'     => $string,
                'clean'    => $filtered,
                'hasMatch' => $this->wasFiltered,
                'matched'  => $this->filteredStrings,
            ];
        }
        return $filtered;
    }
    public function noProfanity($string)
    {
        $this->resetFiltered();
        if (!is_string($string) || !trim($string)) {
            return;
        }
        $filtered = $this->filterString($string);
        foreach ($this->badWords as $badword) {
            if (stripos($string, $badword) !== false) {
                return false;
            }
        }
        return true;
    }
    private function filterString($string)
    {
        return preg_replace_callback($this->filterChecks, function ($matches) {
            return $this->replaceWithFilter($matches[0]);
        }, $string);
    }
    private function setFiltered($string)
    {
        array_push($this->filteredStrings, $string);
        if (!$this->wasFiltered) {
            $this->wasFiltered = true;
        }
    }
    private function replaceWithFilter($string)
    {
        $this->setFiltered($string);
        $strlen = mb_strlen($string);
        if ($this->multiCharReplace) {
            return str_repeat($this->replaceWith, $strlen);
        }
        return $this->randomFilterChar($strlen);
    }
    private function generateFilterChecks()
    {
        foreach ($this->badWords as $string) {
            $this->filterChecks[] = $this->getFilterRegexp($string);
        }
    }
    private function getFilterRegexp($string)
    {
        $replaceFilter = $this->replaceFilter($string);
        if ($this->replaceFullWords) {
            return '/\b'.$replaceFilter.'\b/iu';
        }
        return '/'.$replaceFilter.'/iu';
    }
    private function replaceFilter($string)
    {
        return str_ireplace(array_keys($this->strReplace), array_values($this->strReplace), $string);
    }
    private function randomFilterChar($len)
    {
        return str_shuffle(str_repeat($this->replaceWith, intval($len / $this->replaceWithLength)).substr($this->replaceWith, 0, ($len % $this->replaceWithLength)));
    }
//    const SEPARATOR_PLACEHOLDER = '{!!}';
//    /**
//     * Escaped separator characters
//     */
//    protected $escapedSeparatorCharacters = array(
//        '\s',
//    );
//    /**
//     * Unescaped separator characters.
//     * @var array
//     */
//    protected $separatorCharacters = array(
//        '@',
//        '#',
//        '%',
//        '&',
//        '_',
//        ';',
//        "'",
//        '"',
//        ',',
//        '~',
//        '`',
//        '|',
//        '!',
//        '$',
//        '^',
//        '*',
//        '(',
//        ')',
//        '-',
//        '+',
//        '=',
//        '{',
//        '}',
//        '[',
//        ']',
//        ':',
//        '<',
//        '>',
//        '?',
//        '.',
//        '/',
//    );
//    /**
//     * List of potential character substitutions as a regular expression.
//     *
//     * @var array
//     */
//    protected $characterSubstitutions = array(
//        '/a/' => array(
//            'a',
//            '4',
//            '@',
//            'Á',
//            'á',
//            'À',
//            'Â',
//            'à',
//            'Â',
//            'â',
//            'Ä',
//            'ä',
//            'Ã',
//            'ã',
//            'Å',
//            'å',
//            'æ',
//            'Æ',
//            'α',
//            'Δ',
//            'Λ',
//            'λ',
//        ),
//        '/b/' => array('b', '8', '\\', '3', 'ß', 'Β', 'β'),
//        '/c/' => array('c', 'Ç', 'ç', 'ć', 'Ć', 'č', 'Č', '¢', '€', '<', '(', '{', '©'),
//        '/d/' => array('d', '\\', ')', 'Þ', 'þ', 'Ð', 'ð'),
//        '/e/' => array('e', '3', '€', 'È', 'è', 'É', 'é', 'Ê', 'ê', 'ë', 'Ë', 'ē', 'Ē', 'ė', 'Ė', 'ę', 'Ę', '∑'),
//        '/f/' => array('f', 'ƒ'),
//        '/g/' => array('g', '6', '9'),
//        '/h/' => array('h', 'Η'),
//        '/i/' => array('i', '!', '|', ']', '[', '1', '∫', 'Ì', 'Í', 'Î', 'Ï', 'ì', 'í', 'î', 'ï', 'ī', 'Ī', 'į', 'Į'),
//        '/j/' => array('j'),
//        '/k/' => array('k', 'Κ', 'κ'),
//        '/l/' => array('l', '!', '|', ']', '[', '£', '∫', 'Ì', 'Í', 'Î', 'Ï', 'ł', 'Ł'),
//        '/m/' => array('m'),
//        '/n/' => array('n', 'η', 'Ν', 'Π', 'ñ', 'Ñ', 'ń', 'Ń'),
//        '/o/' => array(
//            'o',
//            '0',
//            'Ο',
//            'ο',
//            'Φ',
//            '¤',
//            '°',
//            'ø',
//            'ô',
//            'Ô',
//            'ö',
//            'Ö',
//            'ò',
//            'Ò',
//            'ó',
//            'Ó',
//            'œ',
//            'Œ',
//            'ø',
//            'Ø',
//            'ō',
//            'Ō',
//            'õ',
//            'Õ',
//        ),
//        '/p/' => array('p', 'ρ', 'Ρ', '¶', 'þ'),
//        '/q/' => array('q'),
//        '/r/' => array('r', '®'),
//        '/s/' => array('s', '5', '$', '§', 'ß', 'Ś', 'ś', 'Š', 'š'),
//        '/t/' => array('t', 'Τ', 'τ'),
//        '/u/' => array('u', 'υ', 'µ', 'û', 'ü', 'ù', 'ú', 'ū', 'Û', 'Ü', 'Ù', 'Ú', 'Ū'),
//        '/v/' => array('v', 'υ', 'ν'),
//        '/w/' => array('w', 'ω', 'ψ', 'Ψ'),
//        '/x/' => array('x', 'Χ', 'χ'),
//        '/y/' => array('y', '¥', 'γ', 'ÿ', 'ý', 'Ÿ', 'Ý'),
//        '/z/' => array('z', 'Ζ', 'ž', 'Ž', 'ź', 'Ź', 'ż', 'Ż'),
//    );
//    /**
//     * List of profanities to test against.
//     *
//     * @var array
//     */
//    protected $profanities = array();
//    protected $separatorExpression;
//    protected $characterExpressions;
//    /**
//     * @param null $config
//     */
//    public function __construct($config = null)
//    {
//        if ($config === null) {
//            $config = CRAFT_VENDOR_PATH . '/owldesign/qarr/src/rules/data/profanities.php';
//        }
//
//        if (is_array($config)) {
//            $this->profanities = $config;
//        } else {
//            $this->profanities = $this->loadProfanitiesFromFile($config);
//        }
//
//        // Get user defined data
//        $newData = Craft::$app->config->getConfigFromFile('qarr');
//        if (isset($newData['rules']['profanity']['data'])) {
//            foreach ($newData['rules']['profanity']['data'] as $word) {
//                ArrayHelper::prependOrAppend($this->profanities, $word, true);
//            }
//        }
//
//        $this->separatorExpression  = $this->generateSeparatorExpression();
//        $this->characterExpressions = $this->generateCharacterExpressions();
//    }
//    /**
//     * Checks string for profanities based on list 'profanities'
//     *
//     * @param $string
//     *
//     * @return bool
//     */
//    public function hasProfanity($string)
//    {
//        if (empty($string)) {
//            return false;
//        }
//        $profanities    = array();
//        $profanityCount = count($this->profanities);
//        for ($i = 0; $i < $profanityCount; $i++) {
//            $profanities[ $i ] = $this->generateProfanityExpression(
//                $this->profanities[ $i ],
//                $this->characterExpressions,
//                $this->separatorExpression
//            );
//        }
//        foreach ($profanities as $profanity) {
//            if ($this->stringHasProfanity($string, $profanity)) {
//                return true;
//            }
//        }
//        return false;
//    }
//    /**
//     * Obfuscates string that contains a 'profanity'.
//     *
//     * @param $string
//     *
//     * @return string
//     */
//    public function obfuscateIfProfane($string)
//    {
//        if ($this->hasProfanity($string)) {
//            $string = str_repeat("*", strlen($string));
//        }
//        return $string;
//    }
//    /**
//     * Generate a regular expression for a particular word
//     *
//     * @param $word
//     * @param $characterExpressions
//     * @param $separatorExpression
//     *
//     * @return mixed
//     */
//    protected function generateProfanityExpression($word, $characterExpressions, $separatorExpression)
//    {
//        $expression = '/' . preg_replace(
//                array_keys($characterExpressions),
//                array_values($characterExpressions),
//                $word
//            ) . '/i';
//        return str_replace(self::SEPARATOR_PLACEHOLDER, $separatorExpression, $expression);
//    }
//    /**
//     * Checks a string against a profanity.
//     *
//     * @param $string
//     * @param $profanity
//     *
//     * @return bool
//     */
//    private function stringHasProfanity($string, $profanity)
//    {
//        return preg_match($profanity, $string) === 1;
//    }
//    /**
//     * Generates the separator regex to test characters in between letters.
//     *
//     * @param array  $characters
//     * @param array  $escapedCharacters
//     * @param string $quantifier
//     *
//     * @return string
//     */
//    private function generateEscapedExpression(
//        array $characters = array(),
//        array $escapedCharacters = array(),
//        $quantifier = '*?'
//    ) {
//        $regex = $escapedCharacters;
//        foreach ($characters as $character) {
//            $regex[] = preg_quote($character, '/');
//        }
//        return '[' . implode('', $regex) . ']' . $quantifier;
//    }
//    /**
//     * Generates the separator regular expression.
//     *
//     * @return string
//     */
//    private function generateSeparatorExpression()
//    {
//        return $this->generateEscapedExpression($this->separatorCharacters, $this->escapedSeparatorCharacters);
//    }
//    /**
//     * Generates a list of regular expressions for each character substitution.
//     *
//     * @return array
//     */
//    protected function generateCharacterExpressions()
//    {
//        $characterExpressions = array();
//        foreach ($this->characterSubstitutions as $character => $substitutions) {
//            $characterExpressions[ $character ] = $this->generateEscapedExpression(
//                    $substitutions,
//                    array(),
//                    '+?'
//                ) . self::SEPARATOR_PLACEHOLDER;
//        }
//        return $characterExpressions;
//    }
//    /**
//     * Load 'profanities' from config file.
//     *
//     * @param $config
//     *
//     * @return array
//     */
    private function loadProfanitiesFromFile($config)
    {
        /** @noinspection PhpIncludeInspection */
        return include($config);
    }
}