<?php
/**
 * QARR plugin for Craft CMS 3.x
 *
 * Questions & Answers and Reviews & Ratings
 *
 * @link      https://owl-design.net
 * @copyright Copyright (c) 2018 Vadim Goncharov
 */

namespace owldesign\qarr\controllers;

use craft\helpers\UrlHelper;
use owldesign\qarr\QARR;

use Craft;
use craft\web\Controller;
use craft\web\View;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class GeolocationsController extends Controller
{
    protected $allowAnonymous = ['actionReset'];

    /**
     * Reset geolocation stats
     *
     * @return mixed
     */
    public function actionReset()
    {
        return QARR::$plugin->geolocations->reset();
    }

}