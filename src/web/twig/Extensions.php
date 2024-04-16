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

use craft\helpers\ArrayHelper;

class Extensions extends \Twig\Extension\AbstractExtension
{
    // Public Methods
    // =========================================================================

    public function getName(): string
    {
        return 'qarr';
    }

    /**
     * @inheritdoc
     */
    public function getFilters(): array
    {
        return [
            new \Twig\TwigFilter('qarr_json_decode', [$this, 'json_decode']),
            new \Twig\TwigFilter('getClass', [$this, 'getClass']),
            new \Twig\TwigFilter('timeAgo', [$this, 'getTimeAgo']),
            new \Twig\TwigFilter('browser', [$this, 'browser']),
            new \Twig\TwigFilter('formatBytes', [$this, 'formatBytes']),
            new \Twig\TwigFilter('valueOutOfArray', [$this, 'valueOutOfArray']),
            new \Twig\TwigFilter('truncate', [$this, 'truncate']),
            new \Twig\TwigFilter('occurrence', [$this, 'occurrence']),
            new \Twig\TwigFilter('toObject', array($this, 'toObject')),
        ];
    }

    /**
     * @param array $array
     * @return object
     */
    public function toObject($array): object
    {
        return (object)$array;
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction('flaggedWords', [$this, 'flaggedWords']),
            new \Twig\TwigFunction('flaggedWords', [$this, 'flaggedWords']),
        ];
    }

    /**
     * Return decoded json object
     *
     * @param $json
     * @return mixed
     */
    public function json_decode($json): mixed
    {
        return json_decode($json);
    }

    /**
     * Get clean element class name
     *
     * @param $object
     * @return string
     */
    public function getClass($object): string
    {
        return (new \ReflectionClass($object))->getShortName();
    }

    /**
     * Get user friendly time ago value
     *
     * @param $time
     * @return string
     */
    public function getTimeAgo($time): string
    {
        $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
        $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

        $now = time();
        $difference = $now - strtotime($time);

        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        if ($difference != 1) {
            $periods[$j] .= "s";
        }

        return "$difference $periods[$j]";
    }

    /**
     * Get browser from user-agent string
     *
     * @param $browser
     */
    public function browser($browser): void
    {
        if (str_contains($browser, 'MSIE')) {
            echo 'Internet explorer';
        } elseif (str_contains($browser, 'Trident')) //For Supporting IE 11
        {
            echo 'Internet explorer';
        } elseif (str_contains($browser, 'Firefox')) {
            echo 'Mozilla Firefox';
        } elseif (str_contains($browser, 'Chrome')) {
            echo 'Google Chrome';
        } elseif (str_contains($browser, 'Opera Mini')) {
            echo "Opera Mini";
        } elseif (str_contains($browser, 'Opera')) {
            echo "Opera";
        } elseif (str_contains($browser, 'Safari')) {
            echo "Safari";
        } else {
            echo 'Unknown';
        }
    }

    /**
     * Format bytes to ...
     *
     * @param $bytes
     * @param int $precision
     * @return string
     */
    public function formatBytes($bytes, int $precision = 0): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= 1024 ** $pow;

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get value from an array
     *
     * @param array $array
     * @param $value
     * @return array
     */
    public function valueOutOfArray(array $array, $value): array
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
    public function truncate($text, int $chars = 120, string $readMore = 'read the rest', $url = null): bool|string
    {
        if (strlen($text) > $chars) {
            $text .= ' ';
            $text = substr($text, 0, $chars);
            $text = substr($text, 0, strrpos($text, ' '));

            if ($url) {
                $url = '<a href="' . $url . '" class="qarr-btn-link" target="_blank">' . $readMore . '</a>';
            }

            $text .= '... ' . ($url ?: $url);
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
    public function occurrence(string $string, array $rules): array|string|null
    {
        $wordList = [];
        $patterns = [];

        foreach ($rules as $rule => $words) {
            foreach ($words as $word) {
                if (!in_array($word, $wordList)) {
                    $patterns[] = '/\b' . $word . '\b/i';
                    ArrayHelper::prependOrAppend($wordList, $word, true);
                }
            }
        }

        return preg_replace($patterns, '<span class="matched-word">$0</span>', $string);
    }

    /**
     * Flagged words array
     *
     * @param array $flags
     * @return array
     */
    public function flaggedWords($flags): array
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