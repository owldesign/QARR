<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\web\twig;

use Craft;
use craft\helpers\StringHelper;
use craft\helpers\ArrayHelper;
use owldesign\qarr\web\assets\Qarr;
use owldesign\qarr\web\twig\Variables as QarrVariables;

class Extensions extends \Twig_Extension
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'qarr';
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('json_decode', [$this, 'json_decode']),
            new \Twig_SimpleFilter('getClass', [$this, 'getClass']),
            new \Twig_SimpleFilter('timeAgo', [$this, 'getTimeAgo']),
            new \Twig_SimpleFilter('browser', [$this, 'browser']),
            new \Twig_SimpleFilter('formatBytes', [$this, 'formatBytes']),
            new \Twig_SimpleFilter('valueOutOfArray', [$this, 'valueOutOfArray']),
            new \Twig_SimpleFilter('truncate', [$this, 'truncate']),
            new \Twig_SimpleFilter('occurrence', [$this, 'occurrence']),
            new \Twig_SimpleFilter('toObject', array($this, 'toObject')),
        ];
    }

    /**
     * @param array $array
     * @return object
     */
    public function toObject($array)
    {
        return (object) $array;
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new \Twig_Function('flaggedWords', [$this, 'flaggedWords']),
        ];
    }

    /**
     * @return array
     */
    public function getGlobals(): array
    {
        return [
            'qarr' => new QarrVariables()
        ];
    }

    /**
     * Return decoded json object
     *
     * @param $json
     * @return mixed
     */
    public function json_decode($json)
    {
        return json_decode($json);
    }

    /**
     * Get clean element class name
     *
     * @param $object
     * @return string
     * @throws \ReflectionException
     */
    public function getClass($object)
    {
        return (new \ReflectionClass($object))->getShortName();
    }

    /**
     * Get user friendly time ago value
     *
     * @param $time
     * @return string
     */
    public function getTimeAgo($time)
    {
        $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
        $lengths = array("60","60","24","7","4.35","12","10");

        $now = time();
        $difference     = $now - strtotime($time);

        for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        if($difference != 1) {
            $periods[$j].= "s";
        }

        return "$difference $periods[$j]";
    }

    /**
     * Get browser from user-agent string
     *
     * @param $browser
     */
    public function browser($browser)
    {
        if(strpos($browser, 'MSIE') !== FALSE)
            echo 'Internet explorer';
        elseif(strpos($browser, 'Trident') !== FALSE) //For Supporting IE 11
            echo 'Internet explorer';
        elseif(strpos($browser, 'Firefox') !== FALSE)
            echo 'Mozilla Firefox';
        elseif(strpos($browser, 'Chrome') !== FALSE)
            echo 'Google Chrome';
        elseif(strpos($browser, 'Opera Mini') !== FALSE)
            echo "Opera Mini";
        elseif(strpos($browser, 'Opera') !== FALSE)
            echo "Opera";
        elseif(strpos($browser, 'Safari') !== FALSE)
            echo "Safari";
        else
            echo 'Unknown';
    }

    /**
     * Format bytes to ...
     *
     * @param $bytes
     * @param int $precision
     * @return string
     */
    public function formatBytes($bytes, $precision = 0)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get value from an array
     *
     * @param array $array
     * @param $value
     * @return array
     */
    public function valueOutOfArray(array $array, $value)
    {
        $result = [];

        foreach ($array as $item) {
            $result[] = $item[$value];
        }

        return $result;
    }

    /**
     * Truncate text
     *
     * @param $text
     * @param int $chars
     * @param string $readMore
     * @param null $url
     * @return bool|string
     */
    function truncate($text, $chars = 120, $readMore = 'read the rest', $url = null) {
        if(strlen($text) > $chars) {
            $text = $text.' ';
            $text = substr($text, 0, $chars);
            $text = substr($text, 0, strrpos($text ,' '));

            if ($url) {
                $url = '<a href="'. $url .'" class="qarr-btn-link" target="_blank">'. $readMore .'</a>';
            }

            $text = $text . '... ' . ($url ? $url : $url);
        }
        return $text;
    }

    /**
     * Find occurrence of word and wrap span tag around it
     *
     * @param string $string
     * @param array $rules
     * @return null|string|string[]
     */
    function occurrence(string $string, array $rules)
    {
        $wordList = [];
        $patterns = [];

        foreach($rules as $rule => $words) {
            foreach ($words as $word) {
                if (!in_array($word, $wordList)) {
                    $patterns[] = '/\b' . $word . '\b/i';
                    ArrayHelper::prependOrAppend($wordList, $word, true);
                }
            }
        }

        $result = preg_replace($patterns, '<span class="matched-word">$0</span>', $string);

        return $result;
    }

    /**
     * Flagged words array
     *
     * @param array $flags
     * @return array
     */
    function flaggedWords($flags)
    {
        $result = [];
        
        if ($flags) {

            foreach ($flags as $flag) {
                $result[$flag->rule->handle] = $flag->details['matched'];
            }
        }

        return $result;
    }
}