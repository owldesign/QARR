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

use craft\helpers\Json;
use owldesign\qarr\QARR;
use owldesign\qarr\elements\Display;
use owldesign\qarr\records\Display as DisplayRecord;


use Craft;
use craft\base\Component;
use craft\helpers\ArrayHelper;
use yii\base\Exception;

class Cookies extends Component
{
    public function set($name = '', $value = '', $expire = 0, $path = '/', $domain = '', $secure = false, $httpOnly = false)
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
        $result = '';
        if (isset($_COOKIE[$name])) {
            $result = $_COOKIE[$name];
        }
        return $result;
    }
}
