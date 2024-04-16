<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\services;

use Craft;
use craft\base\Component;

/**
 */
class Cookies extends Component
{
    public function set($name = '', $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httpOnly = false): void
    {
        if (empty($value)) {
            Craft::$app->response->cookies->remove($name);
        } else {
            $domain = empty($domain) ? Craft::$app->getConfig()->getGeneral()->defaultCookieDomain : $domain;
            $expire = (int)$expire;
            setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
            $_COOKIE[$name] = $value;
        }
    }

    public function get($name = '')
    {
        return $_COOKIE[$name] ?? '';
    }
}
